@extends('layouts.app')

@section('title', $payment->invoice_number . ' | CityCare')

@section('content')
    <x-page-header :title="$payment->invoice_number" subtitle="Cashier billing record and payment details.">
        <x-slot:actions>
            @if(auth()->user()->hasRole(['admin', 'cashier']))
                <a class="btn btn-outline-secondary" href="{{ workspace_route('payments.edit', $payment) }}">Edit</a>
                <form method="POST" action="{{ workspace_route('payments.destroy', $payment) }}" data-confirm="Archive this payment record?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger" type="submit">Archive</button>
                </form>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad">
        <div class="row g-4">
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Patient</dt><dd class="col-sm-8">{{ $payment->patient->full_name }}</dd>
                    <dt class="col-sm-4">Amount</dt><dd class="col-sm-8">{{ number_format($payment->amount) }}</dd>
                    <dt class="col-sm-4">Method</dt><dd class="col-sm-8">{{ $payment->payment_method }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-status-pill :status="$payment->status" /></dd>
                </dl>
            </div>
            <div class="col-md-6">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Reference</dt><dd class="col-sm-8">{{ $payment->reference ?? 'Not recorded' }}</dd>
                    <dt class="col-sm-4">Stripe session</dt><dd class="col-sm-8">{{ $payment->stripe_checkout_session_id ?? 'Not started' }}</dd>
                    <dt class="col-sm-4">Cashier</dt><dd class="col-sm-8">{{ $payment->cashier?->name ?? 'Not assigned' }}</dd>
                    <dt class="col-sm-4">Paid at</dt><dd class="col-sm-8">{{ $payment->paid_at?->format('M d, Y H:i') ?? 'Not paid' }}</dd>
                    <dt class="col-sm-4">Appointment</dt><dd class="col-sm-8">{{ $payment->appointment?->appointment_date?->format('M d, Y') ?? 'No appointment link' }}</dd>
                </dl>
            </div>
            <div class="col-12">
                <strong>Notes</strong>
                <p class="text-muted mb-0">{{ $payment->notes ?? 'No notes recorded.' }}</p>
            </div>
        </div>
    </div>

    @if($payment->items->isNotEmpty())
        <div class="panel mt-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Unit amount</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->items as $item)
                            <tr>
                                <td><strong>{{ $item->description }}</strong></td>
                                <td>{{ number_format($item->unit_amount) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($canPayOnline)
        <div class="row g-3 mt-3">
            <div class="col-lg-6">
                <div class="panel panel-pad h-100">
                    <p class="eyebrow mb-1">Online payment</p>
                    <h2 class="h5">Pay by card or Stripe-supported wallet</h2>
                    <p class="text-muted">
                        Stripe Checkout opens a secure hosted payment page. Enable cards, wallets, and eligible mobile payment methods in the Stripe Dashboard.
                    </p>
                    @if($stripeConfigured)
                        <form method="POST" action="{{ workspace_route('payments.stripe.checkout', $payment) }}">
                            @csrf
                            <button class="btn btn-dark" type="submit">Pay securely with Stripe</button>
                        </form>
                    @else
                        <div class="alert alert-warning mb-0">
                            Stripe keys are not configured. Add <strong>STRIPE_KEY</strong> and <strong>STRIPE_SECRET</strong> to the project .env file.
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panel panel-pad h-100">
                    <p class="eyebrow mb-1">Mobile money</p>
                    <h2 class="h5">Submit MTN or Airtel reference</h2>
                    <p class="text-muted">Use this after sending mobile money. The cashier will verify the transaction and finalize the receipt.</p>
                    <form method="POST" action="{{ workspace_route('payments.mobile-money', $payment) }}" class="row g-2">
                        @csrf
                        <div class="col-sm-5">
                            <label class="form-label" for="payment_method">Network</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                @foreach($mobileMoneyMethods as $method)
                                    <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label" for="reference">Transaction reference</label>
                            <input class="form-control" id="reference" name="reference" value="{{ old('reference') }}" required>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-outline-secondary" type="submit">Submit mobile money payment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
