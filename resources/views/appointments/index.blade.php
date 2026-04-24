@extends('layouts.app')

@section('title', 'Appointments | CityCare')

@section('content')
    <x-page-header title="Appointments" subtitle="Book, update, cancel, and review patient appointments.">
        <x-slot:actions>
            @if(auth()->user()->hasRole(['admin', 'receptionist', 'patient']))
                <a class="btn btn-dark" href="{{ workspace_route('appointments.create') }}">Book appointment</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-lg-4"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search patient, doctor, reason"></div>
            <div class="col-lg-2"><input class="form-control" name="date" type="date" value="{{ request('date') }}"></div>
            <div class="col-lg-3">
                <select class="form-select" name="doctor_id">
                    <option value="">All doctors</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected((string) request('doctor_id') === (string) $doctor->id)>{{ $doctor->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'scheduled', 'available', 'checked_in', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-1 d-grid"><button class="btn btn-outline-secondary" type="submit">Go</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td><strong>{{ $appointment->appointment_date->format('M d, Y') }}</strong><div class="small text-muted">{{ substr($appointment->start_time, 0, 5) }} - {{ substr($appointment->end_time, 0, 5) }}</div></td>
                            <td>{{ $appointment->patient->full_name }}</td>
                            <td>{{ $appointment->doctor->display_name }}</td>
                            <td>{{ $appointment->department->name }}</td>
                            <td><x-status-pill :status="$appointment->status" /></td>
                            <td>{{ $appointment->payment?->status ? ucfirst($appointment->payment->status) : 'No invoice' }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('appointments.show', $appointment) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $appointments->links() }}</div>
    </div>
@endsection
