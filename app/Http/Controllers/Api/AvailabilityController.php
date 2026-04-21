<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\AppointmentSlotService;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function slots(Request $request, Doctor $doctor, AppointmentSlotService $slotService)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'exclude' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'doctor' => $doctor->display_name,
            'date' => $data['date'],
            'slots' => $slotService->availableSlots($doctor, $data['date'], $data['exclude'] ?? null),
        ]);
    }

    public function patients(Request $request)
    {
        $search = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ])['q'] ?? '';

        $patients = Patient::query()
            ->where('status', 'active')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('patient_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->limit(10)
            ->get()
            ->map(fn ($patient) => [
                'id' => $patient->id,
                'label' => $patient->patient_number . ' - ' . $patient->full_name,
                'phone' => $patient->phone,
            ]);

        return response()->json($patients);
    }

    public function doctors(Request $request, AppointmentSlotService $slotService)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $doctors = Doctor::with('department')
            ->where('status', 'active')
            ->orderBy('staff_number')
            ->get()
            ->map(function (Doctor $doctor) use ($data, $slotService, $days) {
                $workingDays = collect($doctor->working_days ?: [1, 2, 3, 4, 5])
                    ->map(fn ($day) => $days[$day])
                    ->values()
                    ->all();

                return [
                    'id' => $doctor->id,
                    'name' => $doctor->display_name,
                    'department' => $doctor->department->name,
                    'working_days' => $workingDays,
                    'shift' => substr($doctor->shift_starts_at, 0, 5) . ' - ' . substr($doctor->shift_ends_at, 0, 5),
                    'slot_minutes' => $doctor->slot_minutes,
                    'available_slots' => $slotService->availableSlots($doctor, $data['date']),
                ];
            });

        return response()->json(['date' => $data['date'], 'doctors' => $doctors]);
    }
}
