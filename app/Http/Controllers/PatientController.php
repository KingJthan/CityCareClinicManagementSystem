<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::query()
            ->when($request->user()->hasRole('doctor') && $request->user()->doctorProfile, function ($query) use ($request) {
                $query->whereHas('appointments', fn ($appointments) => $appointments->where('doctor_id', $request->user()->doctorProfile->id));
            })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('patient_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->gender, fn ($query, $gender) => $query->where('gender', $gender))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.form', ['patient' => new Patient()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $user = null;

            if ($request->boolean('create_user_account')) {
                $user = User::create([
                    'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => Hash::make($data['password']),
                    'role' => 'patient',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
            }

            Patient::create($this->patientPayload($data) + [
                'user_id' => $user?->id,
                'patient_number' => Patient::nextPatientNumber(),
            ]);
        });

        return redirect()->route('patients.index')->with('success', 'Patient record created.');
    }

    public function show(Patient $patient)
    {
        $this->ensureCanView($patient);

        $patient->load(['user', 'appointments.doctor.user', 'appointments.department', 'consultations.doctor.user', 'payments', 'insurances', 'labResults.orderedBy.user', 'vitalSigns.recorder', 'familyHistories', 'prescriptions.drug']);

        return view('patients.show', [
            'patient' => $patient,
            'appointments' => $patient->appointments()->with(['doctor.user', 'department'])->latest('appointment_date')->take(10)->get(),
            'consultations' => $patient->consultations()->with('doctor.user')->latest()->take(10)->get(),
            'payments' => $patient->payments()->latest()->take(10)->get(),
            'insurances' => $patient->insurances()->latest()->take(5)->get(),
            'labResults' => $patient->labResults()->with('orderedBy.user')->latest('resulted_at')->take(10)->get(),
            'vitalSigns' => $patient->vitalSigns()->with('recorder')->latest('recorded_at')->take(10)->get(),
            'familyHistories' => $patient->familyHistories()->latest()->take(10)->get(),
            'prescriptions' => $patient->prescriptions()->with('drug')->latest()->take(10)->get(),
        ]);
    }

    public function edit(Patient $patient)
    {
        return view('patients.form', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $this->validated($request, $patient);

        $patient->update($this->patientPayload($data));
        $patient->user?->update([
            'name' => trim($data['first_name'] . ' ' . $data['last_name']),
            'phone' => $data['phone'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()->route('patients.show', $patient)->with('success', 'Patient record updated.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient record archived.');
    }

    public function profile(Request $request)
    {
        $patient = $request->user()->patientProfile;

        if (!$patient) {
            abort(404, 'No patient profile is linked to this account.');
        }

        return $this->show($patient);
    }

    private function validated(Request $request, ?Patient $patient = null): array
    {
        $emailRule = Rule::unique('patients', 'email')->ignore($patient);

        return $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', new PhoneNumber],
            'email' => ['nullable', 'email', 'max:255', $emailRule],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', new PhoneNumber],
            'allergies' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'create_user_account' => ['nullable', 'boolean'],
            'password' => [$request->boolean('create_user_account') ? 'required' : 'nullable', 'confirmed', 'min:8'],
        ]);
    }

    private function patientPayload(array $data): array
    {
        return [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'allergies' => $data['allergies'] ?? null,
            'status' => $data['status'],
        ];
    }

    private function ensureCanView(Patient $patient): void
    {
        $user = auth()->user();

        if ($user->hasRole('patient') && $patient->user_id !== $user->id) {
            abort(403);
        }

        if ($user->hasRole('doctor')) {
            $doctorId = $user->doctorProfile?->id;

            if (!$doctorId || !$patient->appointments()->where('doctor_id', $doctorId)->exists()) {
                abort(403);
            }
        }
    }
}
