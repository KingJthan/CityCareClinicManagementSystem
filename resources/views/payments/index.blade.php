@extends('layouts.app')

@section('title', 'Billing | CityCare')

@section('content')
    <x-page-header title="Billing" subtitle="Cashier-controlled invoices, payment status, and patient balances.">
        <x-slot:actions>
            @if($canRecordPayments)
                <a class="btn btn-dark" href="{{ workspace_route('payments.create') }}">Record payment</a>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if(auth()->user()->hasRole('patient'))
        <div class="alert alert-info">
            You can pay pending bills online with Stripe Checkout or submit an MTN/Airtel mobile money reference for cashier verification.
        </div>
    @endif

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-lg-5"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search invoice, reference, or patient"></div>
            <div class="col-lg-3">
                <select class="form-select" name="payment_method">
                    <option value="">All methods</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}" @selected(request('payment_method') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach($paymentStatuses as $status)
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
                        <th>Invoice</th>
                        <th>Patient</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Cashier</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td><strong>{{ $payment->invoice_number }}</strong><div class="small text-muted">{{ $payment->created_at->format('M d, Y') }}</div></td>
                            <td>{{ $payment->patient->full_name }}</td>
                            <td>{{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td><x-status-pill :status="$payment->status" /></td>
                            <td>{{ $payment->cashier?->name ?? 'Not assigned' }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('payments.show', $payment) }}">
                                    {{ auth()->user()->hasRole('patient') && $payment->status === 'pending' ? 'Pay' : 'View' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $payments->links() }}</div>
    </div>
@endsection
