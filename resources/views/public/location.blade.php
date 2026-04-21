@extends('layouts.app')

@section('title', 'CityCare Location')

@section('content')
    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-5">
                    <p class="eyebrow">Location</p>
                    <h1 class="section-title">Visit CityCare Medical Centre</h1>
                    <p class="section-copy">Plot 24 Yusuf Lule Road, Kampala. Use directions to open a map route from your current location.</p>
                    <a class="btn btn-dark btn-lg" href="https://www.google.com/maps/dir/?api=1&destination=Plot%2024%20Yusuf%20Lule%20Road%2C%20Kampala" target="_blank" rel="noopener">Get directions</a>
                </div>
                <div class="col-lg-7">
                    <div class="media-card">
                        <img src="{{ asset('images/hospital-building.jpg') }}" alt="CityCare Medical Centre building">
                        <div class="media-card-body">
                            <h2 class="h4">Parking and access</h2>
                            <p class="mb-0">Patient drop-off is at the front entrance. Reception and appointment check-in are on the ground floor.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
