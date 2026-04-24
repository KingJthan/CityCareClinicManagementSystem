<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentSlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentSlotService $slotService)
    {
    }

    public function index(Request $request)
    {
        Appointment::markAvailableForCheckIn();

        $appointments = Appointment::with(['patient', 'doctor.user', 'department', 'payment'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('reason', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($patient) => $patient->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('patient_number', 'like', "%{$search}%"))
                        ->orWhereHas('doctor.user', fn ($doctor) => $doctor->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->doctor_id, fn ($query, $doctorId) => $query->where('doctor_id', $doctorId))
            ->when($request->date, fn ($query, $date) => $query->whereDate('appointment_date', $date));

        $this->scopeForRole($appointments, $request);

        return view('appointments.index', [
            'appointments' => $appointments->orderByDesc('appointment_date')->orderBy('start_time')->paginate(10)->withQueryString(),
            'doctors' => Doctor::with('user')->where('status', 'active')->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('appointments.form', $this->formData(new Appointment([
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => $request->user()->hasRole('patient') ? 'pending' : 'scheduled',
        ]), $request));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data = $this->normalizeForRole($request, $data);
        $doctor = Doctor::findOrFail($data['doctor_id']);

        if (!$this->slotService->isAvailable($doctor, $data['appointment_date'], $data['start_time'])) {
            throw ValidationException::withMessages([
                'start_time' => 'This doctor is not available at the selected time.',
            ]);
        }

        Appointment::create($data + [
            'department_id' => $doctor->department_id,
            'end_time' => $this->slotService->endTimeFor($doctor, $data['start_time']),
            'created_by' => $request->user()->id,
        ]);

        $message = $request->user()->hasRole('patient')
            ? 'Appointment request submitted. The clinic will approve the selected available slot.'
            : 'Appointment booked successfully.';

        return redirect()->to(workspace_route('appointments.index'))->with('success', $message);
    }

    public function show(Request $request, Appointment $appointment)
    {
        $this->ensureCanView($request, $appointment);
        Appointment::markAvailableForCheckIn();
        $appointment->load(['patient', 'doctor.user', 'department', 'payment', 'consultation', 'prescriptions.drug', 'radiologyOrders']);

        return view('appointments.show', [
            'appointment' => $appointment->fresh(['patient', 'doctor.user', 'department', 'payment', 'consultation', 'prescriptions.drug', 'radiologyOrders']),
            'checkInUrl' => URL::temporarySignedRoute('appointments.check-in', now()->addHours(6), ['appointment' => $appointment]),
        ]);
    }

    public function edit(Request $request, Appointment $appointment)
    {
        $this->ensureCanView($request, $appointment);

        return view('appointments.form', $this->formData($appointment, $request));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->ensureCanView($request, $appointment);
        $data = $this->validated($request, $appointment);
        $data = $this->normalizeForRole($request, $data);
        $doctor = Doctor::findOrFail($data['doctor_id']);

        if (!$this->slotService->isAvailable($doctor, $data['appointment_date'], $data['start_time'], $appointment->id)) {
            throw ValidationException::withMessages([
                'start_time' => 'This doctor is not available at the selected time.',
            ]);
        }

        $appointment->update($data + [
            'department_id' => $doctor->department_id,
            'end_time' => $this->slotService->endTimeFor($doctor, $data['start_time']),
        ]);

        return redirect()->to(workspace_route('appointments.show', $appointment))->with('success', 'Appointment updated.');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $this->ensureCanView($request, $appointment);
        $appointment->delete();

        return redirect()->to(workspace_route('appointments.index'))->with('success', 'Appointment archived.');
    }

    private function formData(Appointment $appointment, Request $request): array
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();

        if ($request->user()->hasRole('patient')) {
            $patients = collect([$request->user()->patientProfile])->filter();
        }

        return [
            'appointment' => $appointment,
            'patients' => $patients,
            'doctors' => Doctor::with(['user', 'department'])->where('status', 'active')->orderBy('staff_number')->get(),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'slots' => $appointment->doctor_id && $appointment->appointment_date
                ? $this->slotService->availableSlots($appointment->doctor, $appointment->appointment_date->toDateString(), $appointment->id)
                : [],
        ];
    }

    private function validated(Request $request, ?Appointment $appointment = null): array
    {
        return $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled'])],
            'visit_type' => ['required', 'string', 'max:120'],
            'reason' => ['nullable', 'string'],
            'internal_notes' => ['nullable', 'string'],
        ]);
    }

    private function scopeForRole($query, Request $request): void
    {
        $user = $request->user();

        if ($user->hasRole('doctor') && $user->doctorProfile) {
            $query->where('doctor_id', $user->doctorProfile->id);
        }

        if ($user->hasRole('patient') && $user->patientProfile) {
            $query->where('patient_id', $user->patientProfile->id);
        }
    }

    private function normalizeForRole(Request $request, array $data): array
    {
        if ($request->user()->hasRole('patient')) {
            $patient = $request->user()->patientProfile;

            if (!$patient) {
                abort(403);
            }

            $data['patient_id'] = $patient->id;
            $data['status'] = 'pending';
            $data['internal_notes'] = null;
        }

        return $data;
    }

    private function ensureCanView(Request $request, Appointment $appointment): void
    {
        $user = $request->user();

        if ($user->hasRole('doctor') && $user->doctorProfile?->id !== $appointment->doctor_id) {
            abort(403);
        }

        if ($user->hasRole('patient') && $user->patientProfile?->id !== $appointment->patient_id) {
            abort(403);
        }
    }

    public function checkIn(Request $request, Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor.user']);

        if (!$appointment->isCheckInWindowOpen()) {
            return view('appointments.check-in', [
                'appointment' => $appointment,
                'checkedIn' => false,
                'message' => 'Online check-in opens 30 minutes before the appointment time.',
            ]);
        }

        if (in_array($appointment->status, ['scheduled', 'available'], true)) {
            $appointment->update(['status' => 'checked_in']);
        }

        return view('appointments.check-in', [
            'appointment' => $appointment->fresh(['patient', 'doctor.user']),
            'checkedIn' => true,
            'message' => 'Check-in completed. Please proceed to reception when you arrive.',
        ]);
    }
}
