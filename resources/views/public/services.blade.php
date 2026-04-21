@extends('layouts.app')

@section('title', 'CityCare Services')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <p class="eyebrow">Our services</p>
                    <h1 class="section-title">Care that stays available, organized, and responsive</h1>
                    <p class="section-copy">
                        CityCare combines appointment-based care with 24/7 patient support, ambulance coordination,
                        pharmacy, radiology, and clinical teams that can work from the same patient record.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-dark btn-lg" href="{{ route('shop.index') }}">Choose services</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ route('contact') }}">Book or inquire</a>
                        <a class="btn btn-outline-secondary btn-lg" href="{{ route('location') }}">Get directions</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="media-card">
                        <img src="{{ asset('images/ambulence-team.jpg') }}" alt="CityCare ambulance and emergency response team">
                        <div class="media-card-body">
                            <h2 class="h4">24/7 support and ambulance service</h2>
                            <p class="mb-0">Emergency response coordination helps patients reach care faster when time matters.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4">
                <div class="col-md-6 col-xl-3">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/talk-to-doctor.jpg') }}" alt="Doctor consultation service">
                        <div class="media-card-body">
                            <p class="eyebrow">Outpatient care</p>
                            <h3>Doctor consultations</h3>
                            <p class="mb-0">Scheduled and approved appointments with doctors based on availability.</p>
                            <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('shop.index') }}">Add consultation</a>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-xl-3">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/nurse-team.jpg') }}" alt="Nursing and vital signs service">
                        <div class="media-card-body">
                            <p class="eyebrow">Nursing</p>
                            <h3>Vitals and care support</h3>
                            <p class="mb-0">Nurses and clinical support staff review patient information before care decisions.</p>
                            <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('shop.index') }}">Add nursing support</a>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-xl-3">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/pharmacy.jpg') }}" alt="CityCare pharmacy service">
                        <div class="media-card-body">
                            <p class="eyebrow">Pharmacy</p>
                            <h3>Medication dispensing</h3>
                            <p class="mb-0">Pharmacists manage drug categories, inventory, and doctor prescriptions.</p>
                            <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('shop.index') }}">Add pharmacy review</a>
                        </div>
                    </article>
                </div>
                <div class="col-md-6 col-xl-3">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/your-report.jpg') }}" alt="Lab and radiology results service">
                        <div class="media-card-body">
                            <p class="eyebrow">Diagnostics</p>
                            <h3>Lab and radiology</h3>
                            <p class="mb-0">Blood work, imaging orders, and result notes support better treatment planning.</p>
                            <a class="btn btn-sm btn-outline-secondary mt-3" href="{{ route('shop.index') }}">Add diagnostics</a>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
