<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_excludes_booked_doctor_slots(): void
    {
        [$doctor, $patient] = $this->clinicFixture();
        $date = Carbon::parse('next monday')->toDateString();

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => 'scheduled',
            'visit_type' => 'Consultation',
        ]);

        $response = $this->getJson(route('api.doctors.slots', ['doctor' => $doctor, 'date' => $date], false));

        $response->assertOk()
            ->assertJsonMissing(['start' => '09:00'])
            ->assertJsonFragment(['start' => '09:30']);
    }

    public function test_booking_rejects_overlapping_doctor_slot(): void
    {
        [$doctor, $patient, $receptionist] = $this->clinicFixture();
        $date = Carbon::parse('next monday')->toDateString();

        Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'end_time' => '09:30',
            'status' => 'scheduled',
            'visit_type' => 'Consultation',
        ]);

        $this->actingAs($receptionist)->post(route('appointments.store', [], false), [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
            'status' => 'scheduled',
            'visit_type' => 'Consultation',
            'reason' => 'Follow-up',
        ])->assertSessionHasErrors('start_time');
    }

    private function clinicFixture(): array
    {
        $department = Department::create([
            'name' => 'General Medicine',
            'code' => 'GEN',
            'status' => 'active',
        ]);

        $doctorUser = User::factory()->create([
            'name' => 'Grace Doctor',
            'role' => 'doctor',
            'status' => 'active',
        ]);

        $doctor = Doctor::create([
            'user_id' => $doctorUser->id,
            'department_id' => $department->id,
            'staff_number' => 'CCD-900',
            'license_number' => 'MED-900',
            'specialization' => 'Family Medicine',
            'consultation_fee' => 50000,
            'shift_starts_at' => '09:00',
            'shift_ends_at' => '10:00',
            'slot_minutes' => 30,
            'working_days' => [1],
            'status' => 'active',
        ]);

        $patient = Patient::create([
            'patient_number' => 'CCP-26-0099',
            'first_name' => 'Mariam',
            'last_name' => 'Kato',
            'status' => 'active',
        ]);

        $receptionist = User::factory()->create([
            'role' => 'receptionist',
            'status' => 'active',
        ]);

        return [$doctor, $patient, $receptionist];
    }
}
