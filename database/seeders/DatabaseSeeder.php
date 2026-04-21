<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\BillingProduct;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\DrugCategory;
use App\Models\FamilyHistory;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\RadiologyOrder;
use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->user('CityCare Administrator', 'admin@citycare.test', 'admin');
        $receptionist = $this->user('Amina Reception', 'reception@citycare.test', 'receptionist');
        $cashier = $this->user('Daniel Cashier', 'cashier@citycare.test', 'cashier');
        $pharmacist = $this->user('Peter Pharmacist', 'pharmacist@citycare.test', 'pharmacist', '0703003001');
        $radiology = $this->user('Rita Radiology', 'radiology@citycare.test', 'radiology', '0703003002');
        $rn = $this->user('Rebecca RN', 'rn@citycare.test', 'rn', '0703003003');
        $pct = $this->user('Paul PCT', 'pct@citycare.test', 'pct', '0703003004');
        $housekeeping = $this->user('Harriet Housekeeping', 'housekeeping@citycare.test', 'housekeeping', '0703003005');
        $nurse = $this->user('Nora Nurse', 'nurse@citycare.test', 'nurse', '0703003006');
        $dietary = $this->user('Diana Dietary', 'dietary@citycare.test', 'dietary', '0703003007');

        $departments = collect([
            ['name' => 'General Medicine', 'code' => 'GEN', 'location' => 'Block A, Room 101', 'description' => 'Primary care, triage, and routine consultations.'],
            ['name' => 'Pediatrics', 'code' => 'PED', 'location' => 'Block B, Room 204', 'description' => 'Infant, child, and adolescent medical care.'],
            ['name' => 'Dental Care', 'code' => 'DEN', 'location' => 'Block C, Room 302', 'description' => 'Oral examinations, dental procedures, and hygiene care.'],
            ['name' => 'Laboratory', 'code' => 'LAB', 'location' => 'Block A, Room 112', 'description' => 'Diagnostic sample collection and lab result processing.'],
            ['name' => 'Pharmacy', 'code' => 'PHA', 'location' => 'Block A, Dispensary', 'description' => 'Drug inventory, prescription review, and dispensing.'],
            ['name' => 'Radiology', 'code' => 'RAD', 'location' => 'Block D, Imaging Suite', 'description' => 'Imaging requests, urgent studies, and diagnostic results.'],
            ['name' => 'Nursing', 'code' => 'NUR', 'location' => 'Ward Station', 'description' => 'RN and nursing care coordination.'],
            ['name' => 'Patient Care Techs', 'code' => 'PCT', 'location' => 'Clinical Support Desk', 'description' => 'Patient care technician support and attendance assistance.'],
            ['name' => 'House Keeping', 'code' => 'HKP', 'location' => 'Operations Wing', 'description' => 'Clean rooms, sanitation checks, and facility readiness.'],
            ['name' => 'Dietary', 'code' => 'DIT', 'location' => 'Nutrition Desk', 'description' => 'Dietary planning and meal service coordination.'],
        ])->map(fn ($department) => Department::updateOrCreate(
            ['code' => $department['code']],
            $department + ['status' => 'active']
        ));

        collect([
            [
                'code' => 'CONS-GEN',
                'name' => 'General doctor consultation',
                'category' => 'Consultation',
                'description' => 'A scheduled outpatient review with a CityCare doctor.',
                'price' => 85000,
                'image_path' => 'images/talk-to-doctor.jpg',
            ],
            [
                'code' => 'CONS-PED',
                'name' => 'Pediatric consultation',
                'category' => 'Consultation',
                'description' => 'Child and adolescent care with pediatric review.',
                'price' => 95000,
                'image_path' => 'images/baby-patient.jpg',
            ],
            [
                'code' => 'LAB-BLOOD',
                'name' => 'Blood work panel',
                'category' => 'Diagnostics',
                'description' => 'Routine blood work request with results attached to the patient record.',
                'price' => 60000,
                'image_path' => 'images/your-report.jpg',
            ],
            [
                'code' => 'RAD-REVIEW',
                'name' => 'Radiology review',
                'category' => 'Diagnostics',
                'description' => 'Imaging order support and radiology result review.',
                'price' => 110000,
                'image_path' => 'images/hospital-building.jpg',
            ],
            [
                'code' => 'NUR-VITALS',
                'name' => 'Nursing vitals check',
                'category' => 'Nursing',
                'description' => 'Blood pressure, temperature, pulse, oxygen saturation, and clinical support notes.',
                'price' => 30000,
                'image_path' => 'images/nurse-team.jpg',
            ],
            [
                'code' => 'AMB-247',
                'name' => 'Ambulance response booking',
                'category' => 'Emergency',
                'description' => '24/7 ambulance coordination for urgent movement to CityCare.',
                'price' => 150000,
                'image_path' => 'images/ambulence-team.jpg',
            ],
            [
                'code' => 'PHA-REVIEW',
                'name' => 'Pharmacy prescription review',
                'category' => 'Pharmacy',
                'description' => 'Pharmacist review before dispensing doctor-prescribed medication.',
                'price' => 25000,
                'image_path' => 'images/pharmacy.jpg',
            ],
        ])->each(fn ($product) => BillingProduct::updateOrCreate(
            ['code' => $product['code']],
            $product + ['status' => 'active']
        ));

        $doctorUsers = [
            $this->user('Grace Nansubuga', 'doctor.grace@citycare.test', 'doctor', '0701001001'),
            $this->user('Samuel Okello', 'doctor.samuel@citycare.test', 'doctor', '0701001002'),
            $this->user('Leah Mukasa', 'doctor.leah@citycare.test', 'doctor', '0701001003'),
        ];

        $doctors = collect([
            [
                'user_id' => $doctorUsers[0]->id,
                'department_id' => $departments[0]->id,
                'staff_number' => 'CCD-001',
                'license_number' => 'MED-44218',
                'specialization' => 'Family Physician',
                'consultation_fee' => 85000,
                'room' => 'A-104',
            ],
            [
                'user_id' => $doctorUsers[1]->id,
                'department_id' => $departments[1]->id,
                'staff_number' => 'CCD-002',
                'license_number' => 'PED-11904',
                'specialization' => 'Pediatrician',
                'consultation_fee' => 95000,
                'room' => 'B-207',
            ],
            [
                'user_id' => $doctorUsers[2]->id,
                'department_id' => $departments[2]->id,
                'staff_number' => 'CCD-003',
                'license_number' => 'DEN-73041',
                'specialization' => 'Dental Surgeon',
                'consultation_fee' => 120000,
                'room' => 'C-305',
            ],
        ])->map(fn ($doctor) => Doctor::updateOrCreate(
            ['staff_number' => $doctor['staff_number']],
            $doctor + [
                'shift_starts_at' => '08:00',
                'shift_ends_at' => '17:00',
                'slot_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5],
                'status' => 'active',
            ]
        ));

        $patientUser = $this->user('Mariam Kato', 'patient@citycare.test', 'patient', '0702002001');

        $patients = collect([
            [
                'user_id' => $patientUser->id,
                'patient_number' => 'CCP-26-0001',
                'first_name' => 'Mariam',
                'last_name' => 'Kato',
                'date_of_birth' => '1993-06-15',
                'gender' => 'Female',
                'phone' => '0702002001',
                'email' => 'patient@citycare.test',
                'address' => 'Ntinda, Kampala',
                'emergency_contact_name' => 'Isaac Kato',
                'emergency_contact_phone' => '0702999001',
                'allergies' => 'Penicillin',
            ],
            [
                'patient_number' => 'CCP-26-0002',
                'first_name' => 'Brian',
                'last_name' => 'Tushabe',
                'date_of_birth' => '1987-11-24',
                'gender' => 'Male',
                'phone' => '0702002002',
                'email' => 'brian.tushabe@example.com',
                'address' => 'Kira, Wakiso',
                'emergency_contact_name' => 'Ruth Tushabe',
                'emergency_contact_phone' => '0702999002',
            ],
            [
                'patient_number' => 'CCP-26-0003',
                'first_name' => 'Esther',
                'last_name' => 'Namuli',
                'date_of_birth' => '2018-03-09',
                'gender' => 'Female',
                'phone' => '0702002003',
                'email' => 'esther.guardian@example.com',
                'address' => 'Muyenga, Kampala',
                'emergency_contact_name' => 'Sarah Namuli',
                'emergency_contact_phone' => '0702999003',
            ],
        ])->map(fn ($patient) => Patient::updateOrCreate(
            ['patient_number' => $patient['patient_number']],
            $patient + ['status' => 'active']
        ));

        $firstAppointment = Appointment::updateOrCreate(
            [
                'patient_id' => $patients[0]->id,
                'doctor_id' => $doctors[0]->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
            ],
            [
                'department_id' => $doctors[0]->department_id,
                'end_time' => '09:30',
                'status' => 'scheduled',
                'visit_type' => 'Consultation',
                'reason' => 'Follow-up review for recurring headaches.',
                'internal_notes' => 'Patient prefers morning appointments.',
                'created_by' => $receptionist->id,
            ]
        );

        $completedAppointment = Appointment::updateOrCreate(
            [
                'patient_id' => $patients[1]->id,
                'doctor_id' => $doctors[2]->id,
                'appointment_date' => now()->subDays(2)->toDateString(),
                'start_time' => '11:00',
            ],
            [
                'department_id' => $doctors[2]->department_id,
                'end_time' => '11:30',
                'status' => 'completed',
                'visit_type' => 'Dental review',
                'reason' => 'Tooth sensitivity and gum discomfort.',
                'internal_notes' => 'Completed without complications.',
                'created_by' => $admin->id,
            ]
        );

        Consultation::updateOrCreate(
            ['appointment_id' => $completedAppointment->id],
            [
                'patient_id' => $completedAppointment->patient_id,
                'doctor_id' => $completedAppointment->doctor_id,
                'symptoms' => 'Pain while chewing and mild gum swelling.',
                'diagnosis' => 'Localized gum inflammation.',
                'treatment_plan' => 'Dental cleaning, antiseptic mouth rinse, and review in two weeks.',
                'prescription' => 'Chlorhexidine mouthwash twice daily for 7 days.',
                'next_visit_date' => now()->addDays(12)->toDateString(),
            ]
        );

        PatientInsurance::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'policy_number' => 'JUB-CC-10291'],
            [
                'provider_name' => 'Jubilee Health',
                'member_number' => 'JH-772109',
                'coverage_type' => 'Outpatient and diagnostics',
                'status' => 'active',
                'valid_until' => now()->addYear()->toDateString(),
                'notes' => 'Covers consultation, routine lab work, and imaging pre-authorization.',
            ]
        );

        LabResult::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'test_name' => 'Complete Blood Count'],
            [
                'appointment_id' => $firstAppointment->id,
                'ordered_by' => $doctors[0]->id,
                'category' => 'Blood work',
                'result_value' => 'Normal',
                'unit' => null,
                'reference_range' => 'Within expected range',
                'status' => 'completed',
                'resulted_at' => now()->subDays(8),
                'notes' => 'No abnormal white cell count noted.',
            ]
        );

        LabResult::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'test_name' => 'Fasting Blood Glucose'],
            [
                'appointment_id' => $firstAppointment->id,
                'ordered_by' => $doctors[0]->id,
                'category' => 'Blood work',
                'result_value' => '5.2',
                'unit' => 'mmol/L',
                'reference_range' => '3.9 - 5.5 mmol/L',
                'status' => 'completed',
                'resulted_at' => now()->subDays(8),
                'notes' => 'No immediate diabetic range concern.',
            ]
        );

        VitalSign::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'recorded_at' => now()->subDays(1)->setTime(8, 40)],
            [
                'appointment_id' => $firstAppointment->id,
                'recorded_by' => $nurse->id,
                'blood_pressure' => '128/82',
                'temperature_c' => 36.8,
                'heart_rate' => 78,
                'respiratory_rate' => 16,
                'oxygen_saturation' => 98,
                'weight_kg' => 68.5,
                'notes' => 'Patient alert and oriented.',
            ]
        );

        FamilyHistory::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'condition' => 'High blood pressure'],
            [
                'relationship' => 'Mother',
                'status' => 'known',
                'notes' => 'Mother diagnosed in her late forties.',
            ]
        );

        FamilyHistory::updateOrCreate(
            ['patient_id' => $patients[0]->id, 'condition' => 'Diabetes'],
            [
                'relationship' => 'Father',
                'status' => 'known',
                'notes' => 'Type 2 diabetes reported in father.',
            ]
        );

        $drugCategories = collect([
            ['name' => 'Analgesics', 'code' => 'ALG', 'description' => 'Pain relief medicines.'],
            ['name' => 'Antibiotics', 'code' => 'ANT', 'description' => 'Medicines for bacterial infections.'],
            ['name' => 'Antiseptics', 'code' => 'ASP', 'description' => 'Topical and oral antiseptic products.'],
        ])->map(fn ($category) => DrugCategory::updateOrCreate(
            ['code' => $category['code']],
            $category + ['status' => 'active']
        ));

        $drugs = collect([
            [
                'drug_category_id' => $drugCategories[0]->id,
                'name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'strength' => '500mg',
                'dosage_form' => 'Tablet',
                'unit' => 'tablet',
                'stock_quantity' => 240,
                'reorder_level' => 50,
            ],
            [
                'drug_category_id' => $drugCategories[1]->id,
                'name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'strength' => '250mg',
                'dosage_form' => 'Capsule',
                'unit' => 'capsule',
                'stock_quantity' => 80,
                'reorder_level' => 30,
            ],
            [
                'drug_category_id' => $drugCategories[2]->id,
                'name' => 'Chlorhexidine',
                'generic_name' => 'Chlorhexidine gluconate',
                'strength' => '0.2%',
                'dosage_form' => 'Mouthwash',
                'unit' => 'bottle',
                'stock_quantity' => 18,
                'reorder_level' => 20,
            ],
        ])->map(fn ($drug) => Drug::updateOrCreate(
            [
                'name' => $drug['name'],
                'strength' => $drug['strength'],
                'dosage_form' => $drug['dosage_form'],
            ],
            $drug + ['status' => 'active']
        ));

        Prescription::updateOrCreate(
            [
                'appointment_id' => $completedAppointment->id,
                'drug_id' => $drugs[2]->id,
            ],
            [
                'patient_id' => $completedAppointment->patient_id,
                'doctor_id' => $completedAppointment->doctor_id,
                'prescribed_by' => $doctorUsers[2]->id,
                'dispensed_by' => $pharmacist->id,
                'dosage' => '15ml rinse',
                'frequency' => 'Twice daily',
                'duration' => '7 days',
                'instructions' => 'Do not swallow. Use after brushing.',
                'status' => 'dispensed',
                'dispensed_at' => now()->subDays(2)->setTime(12, 15),
                'pharmacist_notes' => 'Dispensed after payment confirmation.',
            ]
        );

        RadiologyOrder::updateOrCreate(
            [
                'appointment_id' => $firstAppointment->id,
                'study_type' => 'CT head',
            ],
            [
                'patient_id' => $firstAppointment->patient_id,
                'doctor_id' => $firstAppointment->doctor_id,
                'ordered_by' => $doctorUsers[0]->id,
                'handled_by' => $radiology->id,
                'priority' => 'urgent',
                'clinical_notes' => 'Recurring headaches with dizziness. Rule out intracranial abnormality.',
                'status' => 'in_progress',
                'result_notes' => null,
                'resulted_at' => null,
            ]
        );

        Payment::updateOrCreate(
            ['invoice_number' => 'CCI-2604-0001'],
            [
                'appointment_id' => $completedAppointment->id,
                'patient_id' => $completedAppointment->patient_id,
                'cashier_id' => $cashier->id,
                'amount' => 120000,
                'payment_method' => 'MTN Mobile Money',
                'status' => 'paid',
                'reference' => 'MOMO-489201',
                'paid_at' => now()->subDays(2)->setTime(12, 5),
                'notes' => 'Consultation fully settled.',
            ]
        );

        Payment::updateOrCreate(
            ['invoice_number' => 'CCI-2604-0002'],
            [
                'appointment_id' => $firstAppointment->id,
                'patient_id' => $firstAppointment->patient_id,
                'cashier_id' => null,
                'amount' => 85000,
                'payment_method' => 'Airtel Money',
                'status' => 'pending',
                'reference' => null,
                'paid_at' => null,
                'notes' => 'Awaiting response from cashier.',
            ]
        );
    }

    private function user(string $name, string $email, string $role, ?string $phone = null): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'role' => $role,
                'status' => 'active',
                'password' => Hash::make('citycare456'),
                'email_verified_at' => now(),
            ]
        );
    }
}
