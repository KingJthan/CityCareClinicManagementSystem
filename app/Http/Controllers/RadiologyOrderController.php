<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\RadiologyOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RadiologyOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = RadiologyOrder::with(['patient', 'doctor.user', 'appointment'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('study_type', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($patient) => $patient->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->orWhere('patient_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('radiology.index', compact('orders'));
    }

    public function create(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanOrder($request, $appointment);
        $appointment->load(['patient', 'doctor.user']);

        return view('radiology.form', ['appointment' => $appointment]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanOrder($request, $appointment);

        $data = $request->validate([
            'study_type' => ['required', 'string', 'max:120'],
            'priority' => ['required', Rule::in(['routine', 'urgent', 'stat'])],
            'clinical_notes' => ['required', 'string'],
        ]);

        RadiologyOrder::create($data + [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'ordered_by' => $request->user()->id,
            'status' => 'requested',
        ]);

        return redirect()->to(workspace_route('appointments.show', $appointment))->with('success', 'Radiology order sent.');
    }

    public function show(RadiologyOrder $radiologyOrder)
    {
        $radiologyOrder->load(['appointment', 'patient', 'doctor.user', 'orderedBy', 'handledBy']);

        return view('radiology.show', ['order' => $radiologyOrder]);
    }

    public function edit(RadiologyOrder $radiologyOrder)
    {
        $radiologyOrder->load(['patient', 'doctor.user']);

        return view('radiology.result', ['order' => $radiologyOrder]);
    }

    public function update(Request $request, RadiologyOrder $radiologyOrder)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['requested', 'in_progress', 'completed', 'cancelled'])],
            'result_notes' => ['nullable', 'string'],
        ]);

        $payload = $data + ['handled_by' => $request->user()->id];

        if ($data['status'] === 'completed') {
            $payload['resulted_at'] = now();
        }

        $radiologyOrder->update($payload);

        return redirect()->to(workspace_route('radiology-orders.index'))->with('success', 'Radiology order updated.');
    }

    private function ensureDoctorCanOrder(Request $request, Appointment $appointment): void
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
