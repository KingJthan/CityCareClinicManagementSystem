@extends('layouts.app')

@section('title', 'Departments | CityCare')

@section('content')
    <x-page-header title="Departments" subtitle="Manage clinic units, locations, and doctor assignments.">
        <x-slot:actions>
            <a class="btn btn-dark" href="{{ route('departments.create') }}">New department</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-md-6">
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search name, code, or location">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-grid">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Doctors</th>
                        <th>Appointments</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments as $department)
                        <tr>
                            <td><strong>{{ $department->code }}</strong></td>
                            <td>{{ $department->name }}</td>
                            <td>{{ $department->location ?? 'Not set' }}</td>
                            <td>{{ $department->doctors_count }}</td>
                            <td>{{ $department->appointments_count }}</td>
                            <td><x-status-pill :status="$department->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('departments.show', $department) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No departments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $departments->links() }}</div>
    </div>
@endsection
