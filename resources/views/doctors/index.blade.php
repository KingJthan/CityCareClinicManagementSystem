@extends('layouts.app')

@section('title', 'Doctors | CityCare')

@section('content')
    <x-page-header title="Doctors" subtitle="Doctor profiles, departments, consultation rooms, and schedule settings.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('admin'))
                <a class="btn btn-dark" href="{{ route('doctors.create') }}">New doctor</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-lg-5">
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search doctor, license, specialization">
            </div>
            <div class="col-lg-3">
                <select class="form-select" name="department_id">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>
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
            <div class="col-lg-2 d-grid">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Specialization</th>
                        <th>Hours</th>
                        <th>Fee</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                        <tr>
                            <td><strong>{{ $doctor->display_name }}</strong><div class="small text-muted">{{ $doctor->staff_number }}</div></td>
                            <td>{{ $doctor->department->name }}</td>
                            <td>{{ $doctor->specialization }}</td>
                            <td>{{ substr($doctor->shift_starts_at, 0, 5) }} - {{ substr($doctor->shift_ends_at, 0, 5) }}</td>
                            <td>{{ number_format($doctor->consultation_fee) }}</td>
                            <td><x-status-pill :status="$doctor->status" /></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="{{ route('doctors.show', $doctor) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No doctors found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $doctors->links() }}</div>
    </div>
@endsection
