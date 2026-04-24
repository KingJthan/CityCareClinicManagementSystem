@extends('layouts.app')

@section('title', 'Prescription Queue | CityCare')

@section('content')
    <x-page-header title="Prescription Queue" subtitle="Doctor prescriptions sent to pharmacy for pharmacist review and dispensing.">
        <x-slot:actions>
            <a class="btn btn-outline-secondary" href="{{ workspace_route('drugs.index') }}">Drug inventory</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-md-7"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search patient or drug"></div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['pending', 'dispensed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Patient</th><th>Drug</th><th>Doctor</th><th>Dose</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($prescriptions as $prescription)
                        <tr>
                            <td>{{ $prescription->patient->full_name }}<div class="small text-muted">{{ $prescription->patient->patient_number }}</div></td>
                            <td><strong>{{ $prescription->drug->name }}</strong><div class="small text-muted">{{ $prescription->drug->strength }} - {{ $prescription->drug->category->name }}</div></td>
                            <td>{{ $prescription->doctor->display_name }}</td>
                            <td>{{ $prescription->dosage }}<div class="small text-muted">{{ $prescription->frequency ?? 'No frequency' }}</div></td>
                            <td><x-status-pill :status="$prescription->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('prescriptions.show', $prescription) }}">View</a>
                                <a class="btn btn-sm btn-dark" href="{{ workspace_route('prescriptions.edit', $prescription) }}">Process</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No prescriptions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $prescriptions->links() }}</div>
    </div>
@endsection
