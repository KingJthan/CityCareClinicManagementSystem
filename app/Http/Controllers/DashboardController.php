<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Drug;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Prescription;
use App\Models\RadiologyOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Appointment::markAvailableForCheckIn();

        $user = $request->user();
        $doctor = $user->doctorProfile;
        $patient = $user->patientProfile;

        $appointmentsQuery = Appointment::with(['patient', 'doctor.user', 'department'])
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('appointment_date')
            ->orderBy('start_time');

        if ($user->hasRole('doctor') && $doctor) {
            $appointmentsQuery->where('doctor_id', $doctor->id);
        }

        if ($user->hasRole('patient') && $patient) {
            $appointmentsQuery->where('patient_id', $patient->id);
        }

        $recentPayments = collect();

        if ($this->canSeePaymentSummary($request)) {
            $recentPayments = Payment::with('patient')
                ->when($user->hasRole('patient') && $patient, fn ($query) => $query->where('patient_id', $patient->id))
                ->latest()
                ->take(6)
                ->get();
        }

        return view('dashboards.index', [
            'roleLabel' => AuthController::roleLabels()[$user->role] ?? ucfirst($user->role),
            'dashboardSubtitle' => $this->subtitleFor($request),
            'metricCards' => $this->metricCards($request),
            'upcomingAppointments' => $appointmentsQuery->take(8)->get(),
            'recentPayments' => $recentPayments,
            'canSeePaymentSummary' => $this->canSeePaymentSummary($request),
            'roleDuties' => $this->roleDuties($request),
            'doctorWorkloads' => $this->doctorWorkloads($request),
            'attendanceTrend' => $this->attendanceTrend($request),
            'chartData' => $this->chartData($request),
            'doctor' => $doctor,
            'patient' => $patient,
        ]);
    }

    private function metricCards(Request $request): array
    {
        $user = $request->user();
        $doctor = $user->doctorProfile;
        $patient = $user->patientProfile;

        if ($user->hasRole('admin')) {
            return [
                ['label' => 'Today appointments', 'value' => Appointment::whereDate('appointment_date', today())->count(), 'tone' => 'primary'],
                ['label' => 'Active patients', 'value' => Patient::where('status', 'active')->count(), 'tone' => 'success'],
                ['label' => 'Doctors on roster', 'value' => Doctor::where('status', 'active')->count(), 'tone' => 'info'],
                ['label' => 'Pending payments', 'value' => Payment::where('status', 'pending')->count(), 'tone' => 'warning'],
                ['label' => 'Departments', 'value' => Department::where('status', 'active')->count(), 'tone' => 'primary'],
                ['label' => 'Month revenue', 'value' => number_format(Payment::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount')), 'tone' => 'success'],
            ];
        }

        if ($user->hasRole('receptionist')) {
            return [
                ['label' => 'Today appointments', 'value' => Appointment::whereDate('appointment_date', today())->count(), 'tone' => 'primary'],
                ['label' => 'Scheduled visits', 'value' => Appointment::where('status', 'scheduled')->whereDate('appointment_date', '>=', today())->count(), 'tone' => 'info'],
                ['label' => 'Checked in today', 'value' => Appointment::where('status', 'checked_in')->whereDate('appointment_date', today())->count(), 'tone' => 'warning'],
                ['label' => 'Available doctors', 'value' => Doctor::where('status', 'active')->count(), 'tone' => 'success'],
            ];
        }

        if ($user->hasRole('doctor') && $doctor) {
            return [
                ['label' => 'My appointments today', 'value' => Appointment::where('doctor_id', $doctor->id)->whereDate('appointment_date', today())->count(), 'tone' => 'primary'],
                ['label' => 'Upcoming appointments', 'value' => Appointment::where('doctor_id', $doctor->id)->whereDate('appointment_date', '>=', today())->count(), 'tone' => 'info'],
                ['label' => 'Completed visits', 'value' => Appointment::where('doctor_id', $doctor->id)->where('status', 'completed')->count(), 'tone' => 'success'],
                ['label' => 'Patients seen', 'value' => Appointment::where('doctor_id', $doctor->id)->distinct('patient_id')->count('patient_id'), 'tone' => 'warning'],
            ];
        }

        if ($user->hasRole('cashier')) {
            return [
                ['label' => 'Pending payments', 'value' => Payment::where('status', 'pending')->count(), 'tone' => 'warning'],
                ['label' => 'Paid today', 'value' => Payment::where('status', 'paid')->whereDate('paid_at', today())->count(), 'tone' => 'success'],
                ['label' => 'Receipts today', 'value' => number_format(Payment::where('status', 'paid')->whereDate('paid_at', today())->sum('amount')), 'tone' => 'info'],
                ['label' => 'Month revenue', 'value' => number_format(Payment::where('status', 'paid')->whereMonth('paid_at', now()->month)->sum('amount')), 'tone' => 'success'],
            ];
        }

        if ($user->hasRole('pharmacist')) {
            return [
                ['label' => 'Pending prescriptions', 'value' => Prescription::where('status', 'pending')->count(), 'tone' => 'warning'],
                ['label' => 'Dispensed today', 'value' => Prescription::where('status', 'dispensed')->whereDate('dispensed_at', today())->count(), 'tone' => 'success'],
                ['label' => 'Active drugs', 'value' => Drug::where('status', 'active')->count(), 'tone' => 'info'],
                ['label' => 'Low stock drugs', 'value' => Drug::whereColumn('stock_quantity', '<=', 'reorder_level')->count(), 'tone' => 'primary'],
            ];
        }

        if ($user->hasRole('radiology')) {
            return [
                ['label' => 'Requested studies', 'value' => RadiologyOrder::where('status', 'requested')->count(), 'tone' => 'warning'],
                ['label' => 'In progress', 'value' => RadiologyOrder::where('status', 'in_progress')->count(), 'tone' => 'info'],
                ['label' => 'Completed today', 'value' => RadiologyOrder::where('status', 'completed')->whereDate('resulted_at', today())->count(), 'tone' => 'success'],
                ['label' => 'Urgent queue', 'value' => RadiologyOrder::whereIn('priority', ['urgent', 'stat'])->where('status', '!=', 'completed')->count(), 'tone' => 'primary'],
            ];
        }

        if ($user->hasRole('patient') && $patient) {
            return [
                ['label' => 'Upcoming appointments', 'value' => Appointment::where('patient_id', $patient->id)->whereDate('appointment_date', '>=', today())->count(), 'tone' => 'primary'],
                ['label' => 'Completed visits', 'value' => Appointment::where('patient_id', $patient->id)->where('status', 'completed')->count(), 'tone' => 'success'],
                ['label' => 'Pending payments', 'value' => Payment::where('patient_id', $patient->id)->where('status', 'pending')->count(), 'tone' => 'warning'],
                ['label' => 'Paid invoices', 'value' => Payment::where('patient_id', $patient->id)->where('status', 'paid')->count(), 'tone' => 'info'],
            ];
        }

        if ($user->hasRole(['rn', 'pct', 'housekeeping', 'nurse', 'dietary'])) {
            return [
                ['label' => 'Today appointments', 'value' => Appointment::whereDate('appointment_date', today())->count(), 'tone' => 'primary'],
                ['label' => 'Checked in today', 'value' => Appointment::where('status', 'checked_in')->whereDate('appointment_date', today())->count(), 'tone' => 'info'],
                ['label' => 'Active departments', 'value' => Department::where('status', 'active')->count(), 'tone' => 'success'],
                ['label' => 'Completed visits', 'value' => Appointment::where('status', 'completed')->whereDate('appointment_date', today())->count(), 'tone' => 'warning'],
            ];
        }

        return [];
    }

    private function subtitleFor(Request $request): string
    {
        return match ($request->user()->role) {
            'admin' => 'Central monitoring for appointments, doctor workloads, payments, and attendance trends.',
            'receptionist' => 'Book, update, and cancel appointments while checking doctor availability.',
            'doctor' => 'Review your schedule, patient information, consultation notes, and visit history.',
            'cashier' => 'Record and track patient payments, pending invoices, and receipt summaries.',
            'pharmacist' => 'Manage drugs, drug categories, and the prescription queue sent by doctors.',
            'radiology' => 'Process radiology orders, update study statuses, and record imaging results.',
            'rn' => 'Support patient movement, appointment readiness, and care coordination.',
            'pct' => 'Support bedside care tasks and patient flow throughout the clinic.',
            'housekeeping' => 'Track clinic support duties and keep patient areas ready for service.',
            'nurse' => 'Support triage, patient care coordination, and clinic visit readiness.',
            'dietary' => 'Coordinate dietary service support for patients and care teams.',
            'patient' => 'View your profile, upcoming appointments, visit history, and payment status.',
            default => 'CityCare operational dashboard.',
        };
    }

    private function roleDuties(Request $request): array
    {
        return match ($request->user()->role) {
            'admin' => [
                'Register patients and maintain doctor profiles.',
                'Manage departments and consultation schedules.',
                'Monitor daily appointments, doctor workloads, payments, and attendance trends.',
            ],
            'receptionist' => [
                'Book, update, and cancel appointments.',
                'Use dynamic doctor slots to avoid overlapping appointments.',
                'Search patient records before assigning appointments.',
            ],
            'doctor' => [
                'View your appointment schedule.',
                'Open patient details and previous visit history.',
                'Record consultation notes, diagnosis, treatment plan, and prescriptions.',
            ],
            'cashier' => [
                'Record payments made by patients.',
                'Track pending, paid, waived, and refunded invoices.',
                'Review payment summaries without clinical notes.',
            ],
            'pharmacist' => [
                'Maintain pharmacy drug categories and drug inventory.',
                'Receive prescriptions sent by doctors.',
                'Mark prescriptions as dispensed after pharmacist review.',
            ],
            'radiology' => [
                'Review imaging orders sent by doctors.',
                'Update study status from requested to completed.',
                'Record radiology findings for clinical follow-up.',
            ],
            'rn' => [
                'Support patient preparation and clinic care coordination.',
                'Monitor appointment readiness and patient flow.',
                'Escalate care needs to doctors and nurses.',
            ],
            'pct' => [
                'Assist patients with basic care and movement support.',
                'Help maintain visit flow and readiness.',
                'Coordinate with nurses and reception where needed.',
            ],
            'housekeeping' => [
                'Keep consultation and patient areas clean and prepared.',
                'Support infection-control readiness.',
                'Coordinate room readiness with clinic operations.',
            ],
            'nurse' => [
                'Support triage and patient care coordination.',
                'Prepare patients before doctor consultation.',
                'Help monitor patient attendance and clinic flow.',
            ],
            'dietary' => [
                'Coordinate dietary support requests.',
                'Support patient comfort and service readiness.',
                'Work with care teams where dietary needs are identified.',
            ],
            'patient' => [
                'View your personal profile.',
                'Review upcoming appointments and visit history.',
                'Check your payment status.',
            ],
            default => [],
        };
    }

    private function canSeePaymentSummary(Request $request): bool
    {
        return $request->user()->hasRole(['admin', 'cashier', 'patient']);
    }

    private function doctorWorkloads(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            return collect();
        }

        return Doctor::with('user')
            ->withCount([
                'appointments as appointments_today_count' => fn ($query) => $query->whereDate('appointment_date', today()),
                'appointments as upcoming_appointments_count' => fn ($query) => $query->whereDate('appointment_date', '>=', today()),
            ])
            ->orderByDesc('appointments_today_count')
            ->take(6)
            ->get();
    }

    private function attendanceTrend(Request $request): array
    {
        if (!$request->user()->hasRole('admin')) {
            return [];
        }

        return collect(range(6, 0))
            ->map(function (int $daysAgo) {
                $date = today()->subDays($daysAgo);

                return [
                    'label' => $date->format('M d'),
                    'count' => Appointment::whereDate('appointment_date', $date)
                        ->whereIn('status', ['checked_in', 'completed'])
                        ->count(),
                ];
            })
            ->all();
    }

    private function chartData(Request $request): array
    {
        $user = $request->user();
        $doctor = $user->doctorProfile;
        $patient = $user->patientProfile;

        if ($user->hasRole('admin')) {
            return [
                [
                    'title' => 'Appointment performance',
                    'type' => 'pie',
                    'items' => $this->countByStatus(Appointment::query(), ['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled']),
                ],
                [
                    'title' => 'Payment methods',
                    'type' => 'bar',
                    'items' => Payment::query()
                        ->selectRaw('payment_method as label, COUNT(*) as value')
                        ->groupBy('payment_method')
                        ->orderByDesc('value')
                        ->get()
                        ->map(fn ($row) => ['label' => $row->label, 'value' => (int) $row->value])
                        ->all(),
                ],
            ];
        }

        if ($user->hasRole('doctor') && $doctor) {
            return [
                [
                    'title' => 'My schedule status',
                    'type' => 'pie',
                    'items' => $this->countByStatus(Appointment::where('doctor_id', $doctor->id), ['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled']),
                ],
                [
                    'title' => 'Prescription follow-through',
                    'type' => 'bar',
                    'items' => $this->countByStatus(Prescription::where('doctor_id', $doctor->id), ['pending', 'dispensed', 'cancelled']),
                ],
            ];
        }

        if ($user->hasRole('cashier')) {
            return [
                [
                    'title' => 'Payment status',
                    'type' => 'pie',
                    'items' => $this->countByStatus(Payment::query(), ['pending', 'paid', 'waived', 'refunded']),
                ],
            ];
        }

        if ($user->hasRole('patient') && $patient) {
            return [
                [
                    'title' => 'My care activity',
                    'type' => 'pie',
                    'items' => $this->countByStatus(Appointment::where('patient_id', $patient->id), ['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled']),
                ],
            ];
        }

        return [];
    }

    private function countByStatus($query, array $statuses): array
    {
        $counts = (clone $query)
            ->selectRaw('status, COUNT(*) as value')
            ->whereIn('status', $statuses)
            ->groupBy('status')
            ->pluck('value', 'status');

        return collect($statuses)
            ->map(fn ($status) => [
                'label' => str_replace('_', ' ', ucfirst($status)),
                'value' => (int) ($counts[$status] ?? 0),
            ])
            ->filter(fn ($item) => $item['value'] > 0)
            ->values()
            ->all();
    }
}
