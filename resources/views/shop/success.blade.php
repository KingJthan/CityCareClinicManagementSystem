@extends('layouts.app')

@section('title', 'Checkout Complete | CityCare')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <p class="eyebrow">Checkout complete</p>
            <h1 class="section-title">Invoice {{ $payment->invoice_number }}</h1>
            <p class="section-copy mb-0">
                @if($payment->status === 'paid')
                    Your Stripe card payment has been received.
                @else
                    Your payment request is waiting for cashier verification.
                @endif
            </p>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="panel">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payment->items as $item)
                                        <tr>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>UGX {{ number_format($item->line_total) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <aside class="panel panel-pad checkout-summary">
                        <p class="eyebrow">Receipt status</p>
                        <dl class="mb-3">
                            <dt>Patient</dt>
                            <dd>{{ $payment->patient->full_name }}</dd>
                            <dt>Amount</dt>
                            <dd>UGX {{ number_format($payment->amount) }}</dd>
                            <dt>Method</dt>
                            <dd>{{ $payment->payment_method }}</dd>
                            <dt>Status</dt>
                            <dd><x-status-pill :status="$payment->status" /></dd>
                            <dt>Reference</dt>
                            <dd>{{ $payment->reference ?? 'Not recorded' }}</dd>
                        </dl>
                        <a class="btn btn-dark w-100" href="{{ workspace_route('shop.index') }}">View more services</a>
                    </aside>
                </div>
            </div>
        </div>
    </section>
@endsection
