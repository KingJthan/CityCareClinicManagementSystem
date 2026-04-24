<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use App\Rules\PhoneNumber;
use App\Services\WorkspaceSessionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login', [
            'roles' => ['patient' => self::roleLabels()['patient']],
            'portal' => 'patient',
        ]);
    }

    public function showStaffLogin()
    {
        $roles = self::roleLabels();
        unset($roles['patient']);

        return view('auth.login', [
            'roles' => $roles,
            'portal' => 'staff',
        ]);
    }

    public function showStaffRegister()
    {
        return view('auth.staff-register', [
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'roles' => self::staffRegistrationRoles(),
        ]);
    }

    public function login(Request $request, WorkspaceSessionManager $workspaces)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'expected_role' => ['nullable', 'string', Rule::in(array_keys(self::roleLabels()))],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'The login details do not match our records.'])
                ->withInput($request->only('email'));
        }

        if (!$user->isActive()) {
            return back()->withErrors(['email' => 'This account has been deactivated.']);
        }

        if (!empty($credentials['expected_role']) && !$user->hasRole($credentials['expected_role'])) {
            return back()
                ->withErrors(['email' => 'Please use the correct portal for your account role.'])
                ->withInput($request->only('email'));
        }

        if (!$user->email_verified_at) {
            $this->issueEmailVerificationCode($user);
            Auth::logout();
            $request->session()->put('verification_user_id', $user->id);

            return redirect()
                ->route('verification.form')
                ->with('success', 'Please verify your email address before entering CityCare.');
        }

        if ($this->shouldSkipOtpForDemoAccount($user)) {
            $user->update(['last_login_at' => now()]);
            $request->session()->regenerate();
            $workspace = $workspaces->activate($request, $user);

            return redirect()
                ->route('workspace.dashboard', ['workspace' => $workspace])
                ->with('success', 'Welcome back to CityCare.');
        }

        $this->issueLoginOtp($user);
        $request->session()->put('login_otp_user_id', $user->id);

        return redirect()
            ->route('otp.form')
            ->with('success', 'A one-time login code has been sent to your email.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function registerStaff(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', new PhoneNumber],
            'role' => ['required', Rule::in(array_keys(self::staffRegistrationRoles()))],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        if ($data['role'] === 'doctor' && empty($data['department_id'])) {
            return back()
                ->withErrors(['department_id' => 'Doctors require a department selection.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => 'active',
                'email_verified_at' => null,
            ]);

            if ($data['role'] === 'doctor') {
                Doctor::create([
                    'user_id' => $user->id,
                    'department_id' => $data['department_id'],
                    'staff_number' => 'DOC-' . now()->format('ymd') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
                    'license_number' => 'LIC-' . strtoupper(substr(md5($user->email), 0, 10)),
                    'specialization' => 'General Practice',
                    'consultation_fee' => 85000,
                    'shift_starts_at' => '08:00:00',
                    'shift_ends_at' => '17:00:00',
                    'slot_minutes' => 30,
                    'working_days' => [1, 2, 3, 4, 5],
                    'room' => 'Assigned on approval',
                    'status' => 'active',
                ]);
            }

            return $user;
        });

        $this->issueEmailVerificationCode($user);
        $request->session()->put('verification_user_id', $user->id);

        return redirect()
            ->route('verification.form')
            ->with('success', 'Your staff account has been created. Verify your email to continue.');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', new PhoneNumber],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        [$firstName, $lastName] = $this->splitName($data['name']);

        $user = DB::transaction(function () use ($data, $firstName, $lastName) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'patient',
                'status' => 'active',
                'email_verified_at' => null,
            ]);

            Patient::create([
                'user_id' => $user->id,
                'patient_number' => Patient::nextPatientNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'status' => 'active',
            ]);

            return $user;
        });

        $this->issueEmailVerificationCode($user);
        $request->session()->put('verification_user_id', $user->id);

        return redirect()
            ->route('verification.form')
            ->with('success', 'Your patient profile is ready. Verify your email to continue.');
    }

    public function showVerifyEmail(Request $request)
    {
        $user = $this->userFromSession($request, 'verification_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please register or login again to verify your email.');
        }

        return view('auth.verify-email', ['email' => $user->email]);
    }

    public function verifyEmail(Request $request, WorkspaceSessionManager $workspaces)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->userFromSession($request, 'verification_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Your verification session has expired. Please login again.');
        }

        if (!$this->validCode($user->email_verification_code, $user->email_verification_expires_at, $data['code'])) {
            return back()->withErrors(['code' => 'The email verification code is invalid or expired.']);
        }

        $user->update([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
            'last_login_at' => now(),
        ]);

        $request->session()->forget('verification_user_id');
        $request->session()->regenerate();
        $workspace = $workspaces->activate($request, $user);

        return redirect()
            ->route('workspace.dashboard', ['workspace' => $workspace])
            ->with('success', 'Email verified. Welcome to CityCare.');
    }

    public function resendEmailVerification(Request $request)
    {
        $user = $this->userFromSession($request, 'verification_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please register or login again to receive a new code.');
        }

        $this->issueEmailVerificationCode($user);

        return back()->with('success', 'A fresh verification code has been sent.');
    }

    public function showOtp(Request $request)
    {
        $user = $this->userFromSession($request, 'login_otp_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login again to request a one-time code.');
        }

        return view('auth.verify-otp', ['email' => $user->email]);
    }

    public function verifyOtp(Request $request, WorkspaceSessionManager $workspaces)
    {
        $data = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $this->userFromSession($request, 'login_otp_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Your login code session has expired. Please login again.');
        }

        if (!$this->validCode($user->login_otp_code, $user->login_otp_expires_at, $data['code'])) {
            return back()->withErrors(['code' => 'The login code is invalid or expired.']);
        }

        $request->session()->forget('login_otp_remember');

        $user->update([
            'login_otp_code' => null,
            'login_otp_expires_at' => null,
            'last_login_at' => now(),
        ]);

        $request->session()->forget('login_otp_user_id');
        $request->session()->regenerate();
        $workspace = $workspaces->activate($request, $user);

        return redirect()
            ->route('workspace.dashboard', ['workspace' => $workspace])
            ->with('success', 'Welcome back to CityCare.');
    }

    public function resendLoginOtp(Request $request)
    {
        $user = $this->userFromSession($request, 'login_otp_user_id');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login again to request a one-time code.');
        }

        $this->issueLoginOtp($user);

        return back()->with('success', 'A fresh login code has been sent.');
    }

    public function logout(Request $request, WorkspaceSessionManager $workspaces)
    {
        $workspace = $request->route('workspace');
        $remainingWorkspaces = is_string($workspace) && $workspace !== ''
            ? $workspaces->remove($request, $workspace)
            : [];

        Auth::logout();

        if ($remainingWorkspaces === []) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('success', 'Logged out successfully.');
        }

        $request->session()->regenerateToken();

        return redirect()
            ->route('workspace.dashboard', ['workspace' => $workspaces->lastWorkspaceKey($request)])
            ->with('success', 'Logged out successfully.');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password updated.');
    }

    public static function roleLabels(): array
    {
        return [
            'admin' => 'Administrator',
            'receptionist' => 'Receptionist',
            'doctor' => 'Doctor',
            'cashier' => 'Cashier',
            'pharmacist' => 'Pharmacist',
            'radiology' => 'Radiology',
            'rn' => 'RN',
            'pct' => 'PCT',
            'housekeeping' => 'House Keeping',
            'nurse' => 'Nurse',
            'dietary' => 'Dietary',
            'patient' => 'Patient',
        ];
    }

    public static function staffRegistrationRoles(): array
    {
        return [
            'doctor' => 'Doctor',
            'receptionist' => 'Receptionist',
            'cashier' => 'Cashier',
            'pharmacist' => 'Pharmacist',
            'radiology' => 'Radiology',
            'rn' => 'RN',
            'pct' => 'PCT',
            'housekeeping' => 'House Keeping',
            'nurse' => 'Nurse',
            'dietary' => 'Dietary',
        ];
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?? $name, $parts[1] ?? 'Patient'];
    }

    private function shouldSkipOtpForDemoAccount(User $user): bool
    {
        return str_ends_with(strtolower($user->email), '@citycare.test');
    }

    private function issueEmailVerificationCode(User $user): void
    {
        $code = $this->generateCode();

        $user->update([
            'email_verification_code' => $code,
            'email_verification_expires_at' => now()->addMinutes(15),
        ]);

        $this->sendAccessCode(
            $user,
            'CityCare email verification code',
            "Use {$code} to verify your CityCare email address. The code expires in 15 minutes.",
            'email verification',
            $code
        );
    }

    private function issueLoginOtp(User $user): void
    {
        $code = $this->generateCode();

        $user->update([
            'login_otp_code' => $code,
            'login_otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendAccessCode(
            $user,
            'CityCare login code',
            "Use {$code} to complete your CityCare login. The code expires in 10 minutes.",
            'login OTP',
            $code
        );
    }

    private function sendAccessCode(User $user, string $subject, string $message, string $purpose, string $code): void
    {
        try {
            Mail::raw($message, function ($mail) use ($user, $subject) {
                $mail->to($user->email)->subject($subject);
            });
        } catch (Throwable $exception) {
            Log::warning('CityCare access code email could not be sent.', [
                'email' => $user->email,
                'purpose' => $purpose,
                'error' => $exception->getMessage(),
            ]);
        }

        if (app()->environment('local')) {
            Log::info('CityCare access code generated.', [
                'email' => $user->email,
                'purpose' => $purpose,
                'code' => $code,
            ]);
        }
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function validCode(?string $expected, $expiresAt, string $submitted): bool
    {
        if (!$expected || !$expiresAt || now()->greaterThan($expiresAt)) {
            return false;
        }

        return hash_equals($expected, trim($submitted));
    }

    private function userFromSession(Request $request, string $key): ?User
    {
        $userId = $request->session()->get($key);

        return $userId ? User::find($userId) : null;
    }
}
