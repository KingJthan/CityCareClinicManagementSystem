@extends('layouts.app')

@section('title', 'Care Services | CityCare')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-end">
                <div class="col-lg-7">
                    <p class="eyebrow">Care services</p>
                    <h1 class="section-title">Select services and check out securely</h1>
                    <p class="section-copy">
                        Build a care cart for consultations, diagnostics, nursing support, ambulance response, and pharmacy review.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a class="btn btn-dark btn-lg" href="{{ workspace_route('cart.index') }}">
                        View cart
                        @if($cartCount > 0)
                            <span class="badge text-bg-light ms-1">{{ $cartCount }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4">
                @foreach($products as $product)
                    <div class="col-md-6 col-xl-4">
                        <article class="media-card shop-product-card h-100">
                            <img src="{{ asset($product->image_path ?? 'images/patient-care.jpg') }}" alt="{{ $product->name }}">
                            <div class="media-card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <p class="eyebrow mb-1">{{ $product->category }}</p>
                                        <h2 class="h4">{{ $product->name }}</h2>
                                    </div>
                                    <span class="price-chip">UGX {{ number_format($product->price) }}</span>
                                </div>
                                <p>{{ $product->description }}</p>
                                <form method="POST" action="{{ workspace_route('cart.add', $product) }}" class="mt-auto">
                                    @csrf
                                    <div class="d-flex gap-2">
                                        <input class="form-control quantity-input" type="number" min="1" max="10" name="quantity" value="1" aria-label="Quantity">
                                        <button class="btn btn-dark flex-grow-1" type="submit">Add to cart</button>
                                    </div>
                                </form>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
