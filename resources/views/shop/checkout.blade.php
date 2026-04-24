@extends('layouts.app')

@section('title', 'Checkout | CityCare')

@section('content')
    @php
        $defaultName = old('customer_name', $patient?->full_name ?? $user?->name ?? '');
        $defaultEmail = old('customer_email', $patient?->email ?? $user?->email ?? '');
        $defaultPhone = old('customer_phone', $patient?->phone ?? $user?->phone ?? '');
        $selectedMethod = old('payment_method', 'Stripe Checkout');
    @endphp

    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <p class="eyebrow">Checkout</p>
            <h1 class="section-title">Complete your CityCare service order</h1>
            <p class="section-copy mb-0">Stripe opens a secure card page for Visa and other supported cards.</p>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <form method="POST" action="{{ workspace_route('shop.checkout.store') }}" class="row g-4">
                @csrf
                <div class="col-lg-7">
                    <div class="panel panel-pad mb-3">
                        <p class="eyebrow">Patient details</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="customer_name">Full name</label>
                                <input class="form-control" id="customer_name" name="customer_name" value="{{ $defaultName }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="customer_email">Email</label>
                                <input class="form-control" id="customer_email" name="customer_email" type="email" value="{{ $defaultEmail }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="customer_phone">Phone</label>
                                <input class="form-control" id="customer_phone" name="customer_phone" value="{{ $defaultPhone }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="panel panel-pad">
                        <p class="eyebrow">Payment route</p>
                        <div class="payment-method-grid">
                            <label class="payment-method-card">
                                <input type="radio" name="payment_method" value="Stripe Checkout" @checked($selectedMethod === 'Stripe Checkout') @disabled(!$stripeConfigured)>
                                <span>
                                    <strong>Visa card through Stripe</strong>
                                    <small>Secure Stripe-hosted card checkout</small>
                                </span>
                            </label>
                            @foreach($mobileMoneyMethods as $method)
                                <label class="payment-method-card">
                                    <input type="radio" name="payment_method" value="{{ $method }}" @checked($selectedMethod === $method)>
                                    <span>
                                        <strong>{{ $method }}</strong>
                                        <small>Cashier verifies the transaction reference</small>
                                    </span>
                                </label>
                            @endforeach
                            <label class="payment-method-card">
                                <input type="radio" name="payment_method" value="Bank Deposit" @checked($selectedMethod === 'Bank Deposit')>
                                <span>
                                    <strong>Bank deposit</strong>
                                    <small>Cashier verifies the deposit slip or reference</small>
                                </span>
                            </label>
                        </div>

                        @if(!$stripeConfigured)
                            <div class="alert alert-warning mt-3 mb-0">
                                Stripe keys are not configured. Add <strong>STRIPE_KEY</strong> and <strong>STRIPE_SECRET</strong> to use card checkout.
                            </div>
                        @endif

                        <div class="mt-3">
                            <label class="form-label" for="reference">Mobile money or bank reference</label>
                            <input class="form-control" id="reference" name="reference" value="{{ old('reference') }}" placeholder="Required for MTN, Airtel, or bank deposit">
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <aside class="panel panel-pad checkout-summary">
                        <p class="eyebrow">Order summary</p>
                        <div class="checkout-lines">
                            @foreach($items as $item)
                                <div class="checkout-line">
                                    <div>
                                        <strong>{{ $item['product']->name }}</strong>
                                        <span>{{ $item['quantity'] }} x UGX {{ number_format($item['product']->price) }}</span>
                                    </div>
                                    <strong>UGX {{ number_format($item['line_total']) }}</strong>
                                </div>
                            @endforeach
                        </div>
                        <div class="checkout-total">
                            <span>Total</span>
                            <strong>UGX {{ number_format($total) }}</strong>
                        </div>
                        <button class="btn btn-dark btn-lg w-100" type="submit">Complete checkout</button>
                        <a class="btn btn-outline-secondary w-100 mt-2" href="{{ workspace_route('cart.index') }}">Back to cart</a>
                    </aside>
                </div>
            </form>
        </div>
    </section>
@endsection
