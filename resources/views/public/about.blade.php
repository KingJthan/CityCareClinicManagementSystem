@extends('layouts.app')

@section('title', 'About CityCare')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <p class="eyebrow">About CityCare</p>
                    <h1 class="section-title">Connected care with organized clinic operations</h1>
                    <p class="section-copy">
                        CityCare Medical Centre uses one coordinated system for appointments, patient records,
                        clinical notes, pharmacy, radiology, billing, and reporting so care teams can work with
                        accurate information.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="media-card">
                        <img src="{{ asset('images/doctor-team.jpg') }}" alt="CityCare clinical team">
                        <div class="media-card-body">
                            <h2 class="h4">Our focus</h2>
                            <p class="mb-0">Reduce waiting time, prevent double-booking, protect role-based access, and give patients clearer visibility into their care.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
