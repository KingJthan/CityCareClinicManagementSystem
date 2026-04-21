<?php

namespace App\Services;

use App\Models\ClinicDocument;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class DocumentService
{
    public function typeOptionsFor(User $user): array
    {
        return $this->typeOptionsByRole()[$user->role] ?? $this->typeOptionsByRole()['default'];
    }

    public function allTypeLabels(): array
    {
        return collect($this->typeOptionsByRole())
            ->flatMap(fn (array $types) => $types)
            ->all();
    }

    public function viewableQueryFor(User $user): Builder
    {
        $query = ClinicDocument::query();

        if ($user->hasRole('admin')) {
            return $query;
        }

        $patient = $user->patientProfile;

        if ($user->hasRole('patient')) {
            return $query->where(function ($inner) use ($user, $patient) {
                $inner->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->when($patient, fn ($patientQuery) => $patientQuery->orWhere('patient_id', $patient->id));
            });
        }

        if ($user->hasRole('doctor') && $user->doctorProfile) {
            $doctorId = $user->doctorProfile->id;

            return $query->where(function ($inner) use ($user, $doctorId) {
                $inner->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhereHas('patient.appointments', fn ($appointments) => $appointments->where('doctor_id', $doctorId));
            });
        }

        if ($user->hasRole('pharmacist')) {
            return $query->where(function ($inner) use ($user) {
                $inner->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhereHas('patient.prescriptions');
            });
        }

        if ($user->hasRole('radiology')) {
            return $query->where(function ($inner) use ($user) {
                $inner->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhereHas('patient.radiologyOrders');
            });
        }

        if ($user->hasRole(['receptionist', 'cashier', 'rn', 'pct', 'nurse'])) {
            return $query->where(function ($inner) use ($user) {
                $inner->where('owner_user_id', $user->id)
                    ->orWhere('uploaded_by', $user->id)
                    ->orWhereNotNull('patient_id');
            });
        }

        return $query->where(function ($inner) use ($user) {
            $inner->where('owner_user_id', $user->id)
                ->orWhere('uploaded_by', $user->id);
        });
    }

    public function patientOptionsFor(User $user): Collection
    {
        if ($user->hasRole(['admin', 'receptionist', 'cashier', 'rn', 'pct', 'nurse'])) {
            return Patient::where('status', 'active')->orderBy('first_name')->get();
        }

        if ($user->hasRole('patient')) {
            return collect($user->patientProfile ? [$user->patientProfile] : []);
        }

        if ($user->hasRole('doctor') && $user->doctorProfile) {
            return Patient::whereHas('appointments', fn ($appointments) => $appointments->where('doctor_id', $user->doctorProfile->id))
                ->orderBy('first_name')
                ->get();
        }

        if ($user->hasRole('pharmacist')) {
            return Patient::whereHas('prescriptions')->orderBy('first_name')->get();
        }

        if ($user->hasRole('radiology')) {
            return Patient::whereHas('radiologyOrders')->orderBy('first_name')->get();
        }

        return collect();
    }

    public function canUploadForPatient(User $user, ?Patient $patient): bool
    {
        if (!$patient) {
            return true;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->hasRole('patient')) {
            return $user->patientProfile?->id === $patient->id;
        }

        if ($user->hasRole(['receptionist', 'cashier', 'rn', 'pct', 'nurse'])) {
            return true;
        }

        if ($user->hasRole('doctor')) {
            return (bool) $user->doctorProfile
                && $patient->appointments()->where('doctor_id', $user->doctorProfile->id)->exists();
        }

        if ($user->hasRole('pharmacist')) {
            return $patient->prescriptions()->exists();
        }

        if ($user->hasRole('radiology')) {
            return $patient->radiologyOrders()->exists();
        }

        return false;
    }

    public function canView(User $user, ClinicDocument $document): bool
    {
        return $this->viewableQueryFor($user)->whereKey($document->id)->exists();
    }

    public function canDelete(User $user, ClinicDocument $document): bool
    {
        if ($user->hasRole('admin') || $document->uploaded_by === $user->id) {
            return true;
        }

        return $user->hasRole('patient')
            && $user->patientProfile
            && $document->patient_id === $user->patientProfile->id;
    }

    private function typeOptionsByRole(): array
    {
        return [
            'admin' => [
                'patient_consent' => 'Patient consent form',
                'staff_credential' => 'Staff credential',
                'insurance_document' => 'Insurance document',
                'administrative_policy' => 'Administrative policy',
                'other' => 'Other document',
            ],
            'receptionist' => [
                'national_id' => 'Patient national ID',
                'insurance_card' => 'Insurance card',
                'referral_letter' => 'Referral letter',
                'consent_form' => 'Consent form',
                'appointment_attachment' => 'Appointment attachment',
                'other' => 'Other document',
            ],
            'doctor' => [
                'medical_report' => 'Medical report',
                'lab_result' => 'Lab result',
                'clinical_image' => 'Clinical image',
                'treatment_plan' => 'Treatment plan',
                'referral_letter' => 'Referral letter',
                'other' => 'Other document',
            ],
            'cashier' => [
                'payment_proof' => 'Payment proof',
                'bank_deposit_slip' => 'Bank deposit slip',
                'insurance_authorization' => 'Insurance authorization',
                'receipt_attachment' => 'Receipt attachment',
                'other' => 'Other document',
            ],
            'pharmacist' => [
                'prescription_attachment' => 'Prescription attachment',
                'dispensing_note' => 'Dispensing note',
                'drug_authorization' => 'Drug authorization',
                'stock_document' => 'Stock document',
                'other' => 'Other document',
            ],
            'radiology' => [
                'imaging_report' => 'Imaging report',
                'imaging_referral' => 'Imaging referral',
                'radiology_consent' => 'Radiology consent',
                'result_attachment' => 'Result attachment',
                'other' => 'Other document',
            ],
            'rn' => [
                'vitals_sheet' => 'Vitals sheet',
                'care_note' => 'Care note',
                'patient_national_id' => 'Patient national ID',
                'lab_attachment' => 'Lab attachment',
                'other' => 'Other document',
            ],
            'pct' => [
                'patient_care_note' => 'Patient care note',
                'mobility_assessment' => 'Mobility assessment',
                'vitals_sheet' => 'Vitals sheet',
                'other' => 'Other document',
            ],
            'nurse' => [
                'nursing_note' => 'Nursing note',
                'triage_form' => 'Triage form',
                'vitals_sheet' => 'Vitals sheet',
                'patient_national_id' => 'Patient national ID',
                'other' => 'Other document',
            ],
            'housekeeping' => [
                'cleaning_checklist' => 'Cleaning checklist',
                'room_readiness' => 'Room readiness document',
                'incident_note' => 'Incident note',
                'other' => 'Other document',
            ],
            'dietary' => [
                'dietary_plan' => 'Dietary plan',
                'meal_request' => 'Meal request',
                'nutrition_assessment' => 'Nutrition assessment',
                'other' => 'Other document',
            ],
            'patient' => [
                'national_id' => 'National ID',
                'insurance_card' => 'Insurance card',
                'referral_letter' => 'Referral letter',
                'previous_medical_report' => 'Previous medical report',
                'other' => 'Other document',
            ],
            'default' => [
                'support_document' => 'Support document',
                'other' => 'Other document',
            ],
        ];
    }
}
