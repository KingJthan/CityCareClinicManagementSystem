@extends('layouts.app')

@section('title', 'Appointment Check-In | CityCare')

@section('content')
    <div class="auth-wrap">
        <div class="panel panel-pad auth-card">
            <p class="eyebrow mb-1">Appointment check-in</p>
            <h1 class="h3 mb-3">{{ $checkedIn ? 'You are checked in' : 'Check-in not open yet' }}</h1>
            <p class="text-muted">{{ $message }}</p>
            <dl class="row mb-4">
                <dt class="col-sm-4">Patient</dt><dd class="col-sm-8">{{ $appointment->patient->full_name }}</dd>
                <dt class="col-sm-4">Doctor</dt><dd class="col-sm-8">{{ $appointment->doctor->display_name }}</dd>
                <dt class="col-sm-4">Time</dt><dd class="col-sm-8">{{ $appointment->appointment_date->format('M d, Y') }} {{ substr($appointment->start_time, 0, 5) }}</dd>
                <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-status-pill :status="$appointment->status" /></dd>
            </dl>
            <a class="btn btn-dark w-100" href="{{ route('login') }}">Open patient portal</a>
        </div>
    </div>
@endsection
