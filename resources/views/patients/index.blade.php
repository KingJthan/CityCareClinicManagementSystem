@extends('layouts.app')

@section('title', 'Patients | CityCare')

@section('content')
    <x-page-header title="Patients" subtitle="Search, register, and review clinic patient records.">
        <x-slot:actions>
            @if(auth()->user()->hasRole(['admin', 'receptionist']))
                <a class="btn btn-dark" href="{{ route('patients.create') }}">New patient</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-lg-6"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search patient name, number, phone, or email"></div>
            <div class="col-lg-2">
                <select class="form-select" name="gender">
                    <option value="">All genders</option>
                    @foreach(['Female', 'Male', 'Other'] as $gender)
                        <option value="{{ $gender }}" @selected(request('gender') === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid"><button class="btn btn-outline-secondary" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Contact</th>
                        <th>Gender</th>
                        <th>Date of birth</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                        <tr>
                            <td><strong>{{ $patient->full_name }}</strong><div class="small text-muted">{{ $patient->patient_number }}</div></td>
                            <td>{{ $patient->phone ?? 'No phone' }}<div class="small text-muted">{{ $patient->email ?? 'No email' }}</div></td>
                            <td>{{ $patient->gender ?? 'Not set' }}</td>
                            <td>{{ $patient->date_of_birth?->format('M d, Y') ?? 'Not set' }}</td>
                            <td><x-status-pill :status="$patient->status" /></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('patients.show', $patient) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No patients found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $patients->links() }}</div>
    </div>
@endsection
