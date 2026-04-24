<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Drug;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $prescriptions = Prescription::with(['patient', 'doctor.user', 'drug.category'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('dosage', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($patient) => $patient->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('patient_number', 'like', "%{$search}%"))
                        ->orWhereHas('drug', fn ($drug) => $drug->where('name', 'like', "%{$search}%")->orWhere('generic_name', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanPrescribe($request, $appointment);
        $appointment->load(['patient', 'doctor.user']);

        return view('prescriptions.form', [
            'appointment' => $appointment,
            'drugs' => Drug::with('category')->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanPrescribe($request, $appointment);

        $data = $request->validate([
            'drug_id' => ['required', 'exists:drugs,id'],
            'dosage' => ['required', 'string', 'max:120'],
            'frequency' => ['nullable', 'string', 'max:120'],
            'duration' => ['nullable', 'string', 'max:120'],
            'instructions' => ['nullable', 'string'],
        ]);

        Prescription::create($data + [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'prescribed_by' => $request->user()->id,
            'status' => 'pending',
        ]);

        return redirect()->to(workspace_route('appointments.show', $appointment))->with('success', 'Prescription sent to pharmacy.');
    }

    public function show(Prescription $prescription)
    {
        $prescription->load(['appointment', 'patient', 'doctor.user', 'drug.category', 'prescriber', 'dispenser']);

        return view('prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $prescription->load(['patient', 'doctor.user', 'drug.category']);

        return view('prescriptions.dispense', compact('prescription'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'dispensed', 'cancelled'])],
            'pharmacist_notes' => ['nullable', 'string'],
        ]);

        $payload = $data;

        if ($data['status'] === 'dispensed') {
            $payload['dispensed_by'] = $request->user()->id;
            $payload['dispensed_at'] = now();
        }

        $prescription->update($payload);

        return redirect()->to(workspace_route('prescriptions.index'))->with('success', 'Prescription queue updated.');
    }

    private function ensureDoctorCanPrescribe(Request $request, Appointment $appointment): void
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return;
        }

        if (!$user->hasRole('doctor') || $user->doctorProfile?->id !== $appointment->doctor_id) {
            abort(403);
        }
    }
}
