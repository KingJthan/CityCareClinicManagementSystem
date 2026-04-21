<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Drug;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\RadiologyOrder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $availableTypes = $this->availableTypes($request);
        $type = $this->authorizedType($request->get('type', array_key_first($availableTypes)), $availableTypes);
        $dateFrom = $request->get('date_from', today()->toDateString());
        $dateTo = $request->get('date_to', today()->toDateString());

        return view('reports.index', [
            'type' => $type,
            'availableTypes' => $availableTypes,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'rows' => $this->rows($request, $type, $dateFrom, $dateTo),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $availableTypes = $this->availableTypes($request);
        $type = $this->authorizedType($request->get('type', array_key_first($availableTypes)), $availableTypes);
        $dateFrom = $request->get('date_from', today()->toDateString());
        $dateTo = $request->get('date_to', today()->toDateString());
        $rows = $this->rows($request, $type, $dateFrom, $dateTo);
        $filename = 'citycare-' . $type . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($type, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $this->headings($type));

            foreach ($rows as $row) {
                fputcsv($handle, $this->csvRow($type, $row));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function rows(Request $request, string $type, string $dateFrom, string $dateTo)
    {
        $user = $request->user();
        $doctor = $user->doctorProfile;
        $patient = $user->patientProfile;

        return match ($type) {
            'payments' => Payment::with(['patient', 'cashier'])
                ->when($user->hasRole('patient'), fn ($query) => $patient ? $query->where('patient_id', $patient->id) : $query->whereRaw('1 = 0'))
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->latest()
                ->get(),
            'prescriptions' => Prescription::with(['patient', 'doctor.user', 'drug.category'])
                ->when($user->hasRole('doctor'), fn ($query) => $doctor ? $query->where('doctor_id', $doctor->id) : $query->whereRaw('1 = 0'))
                ->when($user->hasRole('patient'), fn ($query) => $patient ? $query->where('patient_id', $patient->id) : $query->whereRaw('1 = 0'))
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->latest()
                ->get(),
            'drugs' => Drug::with('category')
                ->orderBy('name')
                ->get(),
            'radiology' => RadiologyOrder::with(['patient', 'doctor.user'])
                ->when($user->hasRole('doctor'), fn ($query) => $doctor ? $query->where('doctor_id', $doctor->id) : $query->whereRaw('1 = 0'))
                ->when($user->hasRole('patient'), fn ($query) => $patient ? $query->where('patient_id', $patient->id) : $query->whereRaw('1 = 0'))
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->latest()
                ->get(),
            'visits' => Consultation::with(['patient', 'doctor.user', 'appointment'])
                ->when($user->hasRole('doctor'), fn ($query) => $doctor ? $query->where('doctor_id', $doctor->id) : $query->whereRaw('1 = 0'))
                ->when($user->hasRole('patient'), fn ($query) => $patient ? $query->where('patient_id', $patient->id) : $query->whereRaw('1 = 0'))
                ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->latest()
                ->get(),
            default => Appointment::with(['patient', 'doctor.user', 'department'])
                ->when($user->hasRole('doctor'), fn ($query) => $doctor ? $query->where('doctor_id', $doctor->id) : $query->whereRaw('1 = 0'))
                ->when($user->hasRole('patient'), fn ($query) => $patient ? $query->where('patient_id', $patient->id) : $query->whereRaw('1 = 0'))
                ->whereBetween('appointment_date', [$dateFrom, $dateTo])
                ->orderBy('appointment_date')
                ->orderBy('start_time')
                ->get(),
        };
    }

    private function headings(string $type): array
    {
        return match ($type) {
            'payments' => ['Invoice', 'Patient', 'Amount', 'Method', 'Status', 'Cashier', 'Paid At'],
            'prescriptions' => ['Patient', 'Doctor', 'Drug', 'Dosage', 'Frequency', 'Duration', 'Status', 'Dispensed At'],
            'drugs' => ['Drug', 'Category', 'Strength', 'Form', 'Stock', 'Reorder Level', 'Status'],
            'radiology' => ['Patient', 'Doctor', 'Study', 'Priority', 'Status', 'Resulted At'],
            'visits' => ['Patient', 'Doctor', 'Appointment Date', 'Diagnosis', 'Treatment Plan', 'Next Visit'],
            default => ['Date', 'Time', 'Patient', 'Doctor', 'Department', 'Status', 'Reason'],
        };
    }

    private function csvRow(string $type, $row): array
    {
        return match ($type) {
            'payments' => [
                $row->invoice_number,
                $row->patient->full_name,
                $row->amount,
                $row->payment_method,
                $row->status,
                $row->cashier?->name,
                $row->paid_at?->format('Y-m-d H:i'),
            ],
            'prescriptions' => [
                $row->patient->full_name,
                $row->doctor->display_name,
                $row->drug->name . ' ' . $row->drug->strength,
                $row->dosage,
                $row->frequency,
                $row->duration,
                $row->status,
                $row->dispensed_at?->format('Y-m-d H:i'),
            ],
            'drugs' => [
                $row->name,
                $row->category->name,
                $row->strength,
                $row->dosage_form,
                $row->stock_quantity,
                $row->reorder_level,
                $row->status,
            ],
            'radiology' => [
                $row->patient->full_name,
                $row->doctor->display_name,
                $row->study_type,
                $row->priority,
                $row->status,
                $row->resulted_at?->format('Y-m-d H:i'),
            ],
            'visits' => [
                $row->patient->full_name,
                $row->doctor->user->name,
                $row->appointment?->appointment_date?->format('Y-m-d'),
                $row->diagnosis,
                $row->treatment_plan,
                $row->next_visit_date?->format('Y-m-d'),
            ],
            default => [
                $row->appointment_date->format('Y-m-d'),
                $row->start_time,
                $row->patient->full_name,
                $row->doctor->user->name,
                $row->department->name,
                $row->status,
                $row->reason,
            ],
        };
    }

    private function availableTypes(Request $request): array
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return [
                'appointments' => 'Appointments',
                'payments' => 'Payments',
                'visits' => 'Patient visits',
                'prescriptions' => 'Prescriptions',
                'drugs' => 'Drug inventory',
                'radiology' => 'Radiology orders',
            ];
        }

        if ($user->hasRole('cashier')) {
            return ['payments' => 'Payments'];
        }

        if ($user->hasRole('doctor')) {
            return [
                'appointments' => 'My schedule',
                'visits' => 'My patient visits',
                'prescriptions' => 'My prescriptions',
                'radiology' => 'My radiology orders',
            ];
        }

        if ($user->hasRole('pharmacist')) {
            return [
                'prescriptions' => 'Prescription queue',
                'drugs' => 'Drug inventory',
            ];
        }

        if ($user->hasRole('radiology')) {
            return ['radiology' => 'Radiology orders'];
        }

        if ($user->hasRole('patient')) {
            return [
                'appointments' => 'My appointments',
                'visits' => 'My visits',
                'payments' => 'My payments',
                'prescriptions' => 'My prescriptions',
                'radiology' => 'My radiology orders',
            ];
        }

        return ['appointments' => 'Appointments'];
    }

    private function authorizedType(string $type, array $availableTypes): string
    {
        return array_key_exists($type, $availableTypes) ? $type : array_key_first($availableTypes);
    }
}
