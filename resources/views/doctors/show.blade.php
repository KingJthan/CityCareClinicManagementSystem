@extends('layouts.app')

@section('title', $doctor->display_name . ' | CityCare')

@section('content')
    <x-page-header :title="$doctor->display_name" subtitle="Doctor schedule, profile, and upcoming appointments.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('admin'))
                <a class="btn btn-outline-secondary" href="{{ route('doctors.edit', $doctor) }}">Edit</a>
                <form method="POST" action="{{ route('doctors.destroy', $doctor) }}" data-confirm="Archive this doctor profile?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Archive</button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="panel panel-pad h-100">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Department</dt><dd class="col-sm-7">{{ $doctor->department->name }}</dd>
                    <dt class="col-sm-5">Specialization</dt><dd class="col-sm-7">{{ $doctor->specialization }}</dd>
                    <dt class="col-sm-5">License</dt><dd class="col-sm-7">{{ $doctor->license_number }}</dd>
                    <dt class="col-sm-5">Room</dt><dd class="col-sm-7">{{ $doctor->room ?? 'Not set' }}</dd>
                    <dt class="col-sm-5">Fee</dt><dd class="col-sm-7">{{ number_format($doctor->consultation_fee) }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7"><x-status-pill :status="$doctor->status" /></dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="panel">
                <div class="panel-pad border-bottom">
                    <h2 class="h5 mb-0">Upcoming appointments</h2>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Date</th><th>Patient</th><th>Reason</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($upcomingAppointments as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->format('M d, Y') }} {{ substr($appointment->start_time, 0, 5) }}</td>
                                    <td>{{ $appointment->patient->full_name }}</td>
                                    <td>{{ $appointment->reason ?? 'Consultation' }}</td>
                                    <td><x-status-pill :status="$appointment->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No upcoming appointments.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
