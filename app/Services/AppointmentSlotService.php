<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AppointmentSlotService
{
    public function availableSlots(Doctor $doctor, string $date, ?int $excludeAppointmentId = null): array
    {
        $day = Carbon::parse($date);
        $workingDays = $doctor->working_days ?: [1, 2, 3, 4, 5];

        if (!in_array($day->dayOfWeek, $workingDays, true)) {
            return [];
        }

        $start = Carbon::parse($date . ' ' . $doctor->shift_starts_at);
        $end = Carbon::parse($date . ' ' . $doctor->shift_ends_at);
        $slotMinutes = max((int) $doctor->slot_minutes, 15);

        $busyStarts = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->when($excludeAppointmentId, fn ($query) => $query->whereKeyNot($excludeAppointmentId))
            ->pluck('start_time')
            ->map(fn ($time) => Carbon::parse($time)->format('H:i'))
            ->all();

        $slots = [];
        $period = CarbonPeriod::create($start, $slotMinutes . ' minutes', $end->copy()->subMinutes($slotMinutes));

        foreach ($period as $slotStart) {
            $slotEnd = $slotStart->copy()->addMinutes($slotMinutes);
            $value = $slotStart->format('H:i');

            if (!in_array($value, $busyStarts, true)) {
                $slots[] = [
                    'start' => $value,
                    'end' => $slotEnd->format('H:i'),
                    'label' => $slotStart->format('g:i A') . ' - ' . $slotEnd->format('g:i A'),
                ];
            }
        }

        return $slots;
    }

    public function isAvailable(Doctor $doctor, string $date, string $startTime, ?int $excludeAppointmentId = null): bool
    {
        $normalizedStart = Carbon::parse($startTime)->format('H:i');

        return collect($this->availableSlots($doctor, $date, $excludeAppointmentId))
            ->contains(fn ($slot) => $slot['start'] === $normalizedStart);
    }

    public function endTimeFor(Doctor $doctor, string $startTime): string
    {
        return Carbon::parse($startTime)
            ->addMinutes(max((int) $doctor->slot_minutes, 15))
            ->format('H:i');
    }
}
