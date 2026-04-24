@extends('layouts.app')

@section('title', 'Radiology Orders | CityCare')

@section('content')
    <x-page-header title="Radiology Orders" subtitle="Track imaging requests, urgent studies, and completed results from one queue." />

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-md-7">
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search study type, patient, or patient number">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['requested', 'in_progress', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-outline-secondary" type="submit">Filter</button>
            </div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Study</th>
                        <th>Doctor</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->patient->full_name }}<div class="small text-muted">{{ $order->patient->patient_number }}</div></td>
                            <td><strong>{{ $order->study_type }}</strong><div class="small text-muted">{{ $order->appointment?->appointment_date?->format('M d, Y') ?? 'No appointment date' }}</div></td>
                            <td>{{ $order->doctor->display_name }}</td>
                            <td>{{ ucfirst($order->priority) }}</td>
                            <td><x-status-pill :status="$order->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('radiology-orders.show', $order) }}">View</a>
                                <a class="btn btn-sm btn-dark" href="{{ workspace_route('radiology-orders.edit', $order) }}">Update</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No radiology orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $orders->links() }}</div>
    </div>
@endsection
