@extends('layouts.app')

@section('title', 'Appointment | CityCare')

@section('content')
    <x-page-header :title="$appointment->patient->full_name . ' Appointment'" :subtitle="$appointment->appointment_date->format('M d, Y') . ' at ' . substr($appointment->start_time, 0, 5)">
        <x-slot:actions>
            @if(auth()->user()->hasRole(['admin', 'receptionist']))
                <a class="btn btn-outline-secondary" href="{{ route('appointments.edit', $appointment) }}">Edit</a>
            @endif
            @if(auth()->user()->hasRole(['admin', 'doctor']) && (!$appointment->consultation || auth()->user()->hasRole('admin') || auth()->user()->doctorProfile?->id === $appointment->doctor_id))
                <a class="btn btn-dark" href="{{ route('consultations.edit', $appointment) }}">Consultation notes</a>
            @endif
            @if(auth()->user()->hasRole(['admin', 'doctor']) && (auth()->user()->hasRole('admin') || auth()->user()->doctorProfile?->id === $appointment->doctor_id))
                <a class="btn btn-outline-secondary" href="{{ route('prescriptions.create', $appointment) }}">Prescribe drug</a>
                <a class="btn btn-outline-secondary" href="{{ route('radiology-orders.create', $appointment) }}">Radiology order</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="panel panel-pad h-100">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Patient</dt><dd class="col-sm-8"><a href="{{ auth()->user()->hasRole('patient') ? route('patients.profile') : route('patients.show', $appointment->patient) }}">{{ $appointment->patient->full_name }}</a></dd>
                    <dt class="col-sm-4">Doctor</dt><dd class="col-sm-8">{{ $appointment->doctor->display_name }}</dd>
                    <dt class="col-sm-4">Department</dt><dd class="col-sm-8">{{ $appointment->department->name }}</dd>
                    <dt class="col-sm-4">Time</dt><dd class="col-sm-8">{{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-status-pill :status="$appointment->status" /></dd>
                    <dt class="col-sm-4">Reason</dt><dd class="col-sm-8">{{ $appointment->reason ?? 'Not recorded' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="panel panel-pad h-100">
                <h2 class="h5">Consultation and billing</h2>
                @if($appointment->consultation)
                    <p><strong>Diagnosis:</strong> {{ $appointment->consultation->diagnosis }}</p>
                    <p><strong>Treatment plan:</strong> {{ $appointment->consultation->treatment_plan }}</p>
                    <p class="mb-0"><strong>Prescription:</strong> {{ $appointment->consultation->prescription ?? 'None recorded' }}</p>
                @else
                    <p class="text-muted">No consultation notes have been recorded yet.</p>
                @endif
                <hr>
                @if($appointment->payment)
                    <p class="mb-1"><strong>Invoice:</strong> <a href="{{ route('payments.show', $appointment->payment) }}">{{ $appointment->payment->invoice_number }}</a></p>
                    <p class="mb-0"><strong>Payment status:</strong> <x-status-pill :status="$appointment->payment->status" /></p>
                @else
                    <p class="text-muted mb-0">No payment record has been created for this appointment.</p>
                @endif
            </div>
        </div>
        @if(auth()->user()->hasRole(['admin', 'receptionist', 'patient']))
            <div class="col-lg-5">
                <div class="panel panel-pad h-100">
                    <h2 class="h5">Patient check-in</h2>
                    <p class="text-muted">Patients can scan this QR code or open the secure link. Check-in opens 30 minutes before the appointment.</p>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <img class="qr-code" src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($checkInUrl) }}" alt="Appointment check-in QR code">
                        <div>
                            <a class="btn btn-outline-secondary" href="{{ $checkInUrl }}">Open check-in link</a>
                            <div class="small text-muted mt-2">Status: <x-status-pill :status="$appointment->status" /></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-lg-6">
            <div class="panel panel-pad h-100">
                <h2 class="h5">Prescriptions sent to pharmacy</h2>
                @forelse($appointment->prescriptions as $prescription)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold">{{ $prescription->drug->name }} {{ $prescription->drug->strength }}</div>
                        <div class="small text-muted">{{ $prescription->dosage }} {{ $prescription->frequency ? '- ' . $prescription->frequency : '' }} {{ $prescription->duration ? '- ' . $prescription->duration : '' }}</div>
                        <div class="mt-1"><x-status-pill :status="$prescription->status" /></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No prescriptions have been sent to pharmacy.</p>
                @endforelse
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel panel-pad h-100">
                <h2 class="h5">Radiology orders</h2>
                @forelse($appointment->radiologyOrders as $order)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-semibold">{{ $order->study_type }}</div>
                        <div class="small text-muted">{{ ucfirst($order->priority) }} priority</div>
                        <div class="mt-1"><x-status-pill :status="$order->status" /></div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No radiology orders have been requested.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
