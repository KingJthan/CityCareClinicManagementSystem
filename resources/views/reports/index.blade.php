@extends('layouts.app')

@section('title', 'Reports | CityCare')

@section('content')
    <x-page-header title="Reports" subtitle="Generate role-based summaries for the work assigned to your account.">
        <x-slot:actions>
            <a class="btn btn-dark" href="{{ workspace_route('reports.export', request()->query()) }}">Export CSV</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-md-3">
                <select class="form-select" name="type">
                    @foreach($availableTypes as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><input class="form-control" name="date_from" type="date" value="{{ $dateFrom }}"></div>
            <div class="col-md-3"><input class="form-control" name="date_to" type="date" value="{{ $dateTo }}"></div>
            <div class="col-md-3 d-grid"><button class="btn btn-outline-secondary" type="submit">Generate</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                @if($type === 'payments')
                    <thead><tr><th>Invoice</th><th>Patient</th><th>Amount</th><th>Method</th><th>Status</th><th>Paid at</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->invoice_number }}</td>
                                <td>{{ $row->patient->full_name }}</td>
                                <td>{{ number_format($row->amount) }}</td>
                                <td>{{ $row->payment_method }}</td>
                                <td><x-status-pill :status="$row->status" /></td>
                                <td>{{ $row->paid_at?->format('M d, Y H:i') ?? 'Not paid' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($type === 'prescriptions')
                    <thead><tr><th>Patient</th><th>Doctor</th><th>Drug</th><th>Dosage</th><th>Status</th><th>Dispensed</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->patient->full_name }}</td>
                                <td>{{ $row->doctor->display_name }}</td>
                                <td>{{ $row->drug->name }} {{ $row->drug->strength }}<div class="small text-muted">{{ $row->drug->category->name }}</div></td>
                                <td>{{ $row->dosage }}<div class="small text-muted">{{ $row->frequency ?? 'No frequency' }}</div></td>
                                <td><x-status-pill :status="$row->status" /></td>
                                <td>{{ $row->dispensed_at?->format('M d, Y H:i') ?? 'Not dispensed' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($type === 'drugs')
                    <thead><tr><th>Drug</th><th>Category</th><th>Strength</th><th>Form</th><th>Stock</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->name }}<div class="small text-muted">{{ $row->generic_name ?? 'No generic name' }}</div></td>
                                <td>{{ $row->category->name }}</td>
                                <td>{{ $row->strength }}</td>
                                <td>{{ $row->dosage_form }}</td>
                                <td>{{ $row->stock_quantity }} {{ $row->unit }}<div class="small text-muted">Reorder at {{ $row->reorder_level }}</div></td>
                                <td><x-status-pill :status="$row->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($type === 'radiology')
                    <thead><tr><th>Patient</th><th>Doctor</th><th>Study</th><th>Priority</th><th>Status</th><th>Resulted</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->patient->full_name }}</td>
                                <td>{{ $row->doctor->display_name }}</td>
                                <td>{{ $row->study_type }}</td>
                                <td>{{ ucfirst($row->priority) }}</td>
                                <td><x-status-pill :status="$row->status" /></td>
                                <td>{{ $row->resulted_at?->format('M d, Y H:i') ?? 'Not resulted' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @elseif($type === 'visits')
                    <thead><tr><th>Date</th><th>Patient</th><th>Doctor</th><th>Diagnosis</th><th>Next visit</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->created_at->format('M d, Y') }}</td>
                                <td>{{ $row->patient->full_name }}</td>
                                <td>{{ $row->doctor->display_name }}</td>
                                <td>{{ $row->diagnosis }}</td>
                                <td>{{ $row->next_visit_date?->format('M d, Y') ?? 'Not set' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @else
                    <thead><tr><th>Date</th><th>Time</th><th>Patient</th><th>Doctor</th><th>Department</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row->appointment_date->format('M d, Y') }}</td>
                                <td>{{ substr($row->start_time, 0, 5) }}</td>
                                <td>{{ $row->patient->full_name }}</td>
                                <td>{{ $row->doctor->display_name }}</td>
                                <td>{{ $row->department->name }}</td>
                                <td><x-status-pill :status="$row->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No report rows found.</td></tr>
                        @endforelse
                    </tbody>
                @endif
            </table>
        </div>
    </div>
@endsection
