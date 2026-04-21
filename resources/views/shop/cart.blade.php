@extends('layouts.app')

@section('title', 'Care Cart | CityCare')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-end">
                <div class="col-lg-8">
                    <p class="eyebrow">Care cart</p>
                    <h1 class="section-title">Review selected CityCare services</h1>
                    <p class="section-copy mb-0">Card checkout is handled by Stripe. Mobile money and bank deposits are sent to cashier verification.</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="btn btn-outline-secondary btn-lg" href="{{ route('shop.index') }}">Add more services</a>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            @if($items->isEmpty())
                <div class="panel panel-pad text-center">
                    <h2 class="h4">Your cart is empty</h2>
                    <p class="text-muted">Select a consultation, diagnostic service, nursing support, or ambulance service.</p>
                    <a class="btn btn-dark" href="{{ route('shop.index') }}">View care services</a>
                </div>
            @else
                <div class="row g-4">
                    <div class="col-lg-8">
                        <div class="panel">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Service</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $item)
                                            @php($product = $item['product'])
                                            <tr>
                                                <td>
                                                    <strong>{{ $product->name }}</strong>
                                                    <div class="small text-muted">{{ $product->category }}</div>
                                                </td>
                                                <td>UGX {{ number_format($product->price) }}</td>
                                                <td>
                                                    <form method="POST" action="{{ route('cart.update', $product) }}" class="cart-quantity-form">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input class="form-control quantity-input" type="number" min="0" max="10" name="quantity" value="{{ $item['quantity'] }}" aria-label="Quantity">
                                                        <button class="btn btn-sm btn-outline-secondary" type="submit">Update</button>
                                                    </form>
                                                </td>
                                                <td><strong>UGX {{ number_format($item['line_total']) }}</strong></td>
                                                <td class="text-end">
                                                    <form method="POST" action="{{ route('cart.remove', $product) }}" data-confirm="Remove this service from the cart?">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <aside class="panel panel-pad checkout-summary">
                            <p class="eyebrow">Summary</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span>Total</span>
                                <strong class="h4 mb-0">UGX {{ number_format($total) }}</strong>
                            </div>
                            <a class="btn btn-dark w-100" href="{{ route('shop.checkout') }}">Continue to checkout</a>
                        </aside>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
