<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function edit(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanManage($request, $appointment);
        $appointment->load(['patient', 'doctor.user', 'department', 'consultation']);

        return view('consultations.form', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $this->ensureDoctorCanManage($request, $appointment);

        $data = $request->validate([
            'symptoms' => ['nullable', 'string'],
            'diagnosis' => ['required', 'string'],
            'treatment_plan' => ['required', 'string'],
            'prescription' => ['nullable', 'string'],
            'next_visit_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        Consultation::updateOrCreate(
            ['appointment_id' => $appointment->id],
            $data + [
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
            ]
        );

        $appointment->update(['status' => 'completed']);

        return redirect()->to(workspace_route('appointments.show', $appointment))->with('success', 'Consultation notes saved.');
    }

    private function ensureDoctorCanManage(Request $request, Appointment $appointment): void
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
