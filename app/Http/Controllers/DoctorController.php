<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use App\Rules\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $doctors = Doctor::with(['user', 'department'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('staff_number', 'like', "%{$search}%")
                        ->orWhere('license_number', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($request->department_id, fn ($query, $departmentId) => $query->where('department_id', $departmentId))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('doctors.index', [
            'doctors' => $doctors,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('doctors.form', [
            'doctor' => new Doctor(['working_days' => [1, 2, 3, 4, 5], 'slot_minutes' => 30]),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'user' => new User(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => 'doctor',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            Doctor::create($this->doctorPayload($data) + ['user_id' => $user->id]);
        });

        return redirect()->to(workspace_route('doctors.index'))->with('success', 'Doctor profile created.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'department', 'appointments.patient'])
            ->loadCount('appointments');

        $upcomingAppointments = $doctor->appointments()
            ->with('patient')
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->take(10)
            ->get();

        return view('doctors.show', compact('doctor', 'upcomingAppointments'));
    }

    public function edit(Doctor $doctor)
    {
        return view('doctors.form', [
            'doctor' => $doctor->load('user'),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'user' => $doctor->user,
        ]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $this->validated($request, $doctor);

        DB::transaction(function () use ($data, $doctor) {
            $userPayload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['user_status'],
            ];

            if (!empty($data['password'])) {
                $userPayload['password'] = Hash::make($data['password']);
            }

            $doctor->user->update($userPayload);
            $doctor->update($this->doctorPayload($data));
        });

        return redirect()->to(workspace_route('doctors.show', $doctor))->with('success', 'Doctor profile updated.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        $doctor->user->update(['status' => 'inactive']);

        return redirect()->to(workspace_route('doctors.index'))->with('success', 'Doctor profile archived.');
    }

    private function validated(Request $request, ?Doctor $doctor = null): array
    {
        $userId = $doctor?->user_id;

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', new PhoneNumber],
            'password' => [$doctor ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'department_id' => ['required', 'exists:departments,id'],
            'staff_number' => ['required', 'string', 'max:50', Rule::unique('doctors', 'staff_number')->ignore($doctor)],
            'license_number' => ['required', 'string', 'max:80', Rule::unique('doctors', 'license_number')->ignore($doctor)],
            'specialization' => ['required', 'string', 'max:255'],
            'consultation_fee' => ['required', 'numeric', 'min:0'],
            'shift_starts_at' => ['required', 'date_format:H:i'],
            'shift_ends_at' => ['required', 'date_format:H:i', 'after:shift_starts_at'],
            'slot_minutes' => ['required', 'integer', 'min:15', 'max:120'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['integer', 'between:0,6'],
            'room' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'user_status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);
    }

    private function doctorPayload(array $data): array
    {
        return [
            'department_id' => $data['department_id'],
            'staff_number' => $data['staff_number'],
            'license_number' => $data['license_number'],
            'specialization' => $data['specialization'],
            'consultation_fee' => $data['consultation_fee'],
            'shift_starts_at' => $data['shift_starts_at'],
            'shift_ends_at' => $data['shift_ends_at'],
            'slot_minutes' => $data['slot_minutes'],
            'working_days' => array_map('intval', $data['working_days'] ?? []),
            'room' => $data['room'] ?? null,
            'status' => $data['status'],
        ];
    }
}
