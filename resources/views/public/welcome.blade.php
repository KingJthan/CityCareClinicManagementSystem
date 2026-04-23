@extends('layouts.app')

@section('title', 'CityCare Medical Centre')

@section('content')
    <section class="public-hero">
        <div class="container-fluid px-3 px-lg-4">
            <div class="public-hero-grid">
                <div class="public-hero-copy">
                    <p class="eyebrow text-white-50">Clinic Appointment and Patient Management System</p>
                    <h1>CityCare Medical Centre</h1>
                    <p class="mt-3">
                        A professional digital front desk for doctor availability, 24/7 patient support,
                        ambulance response, and organized clinic operations.
                    </p>
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <a class="btn btn-light btn-lg" href="{{ route('services') }}">View services</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ route('shop.index') }}">Care shop</a>
                        <a class="btn btn-outline-light btn-lg" href="{{ route('contact') }}">Book or inquire</a>
                    </div>
                    <div class="public-tag-row mt-4">
                        <a href="{{ route('features.role-access') }}">Role-based access</a>
                        <a href="{{ route('features.doctor-slots') }}">Live doctor slot checks</a>
                        <a href="{{ route('features.services-24-7') }}">24/7 services</a>
                        <a href="{{ route('features.ambulance-support') }}">Ambulance support</a>
                    </div>
                </div>

                <aside class="public-patient-panel" aria-label="Patient portal access">
                    <p class="eyebrow text-white-50">Patient access</p>
                    <h2>Book care faster</h2>
                    <p>Patients can sign in separately to request appointments and manage their clinic profile.</p>
                    <div class="d-grid gap-2">
                        <a class="btn btn-light" href="{{ route('login') }}">Patient login</a>
                        <a class="btn btn-outline-light" href="{{ route('register') }}">Patient sign up</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <section class="public-band public-band-tight">
        <div class="container-fluid px-3 px-lg-4">
            <div class="public-stat-grid">
                <div class="public-stat">
                    <span class="public-stat-label">24/7 clinic support</span>
                    <strong>Always reachable</strong>
                    <p class="mb-0">Patients can reach CityCare for urgent guidance, appointment help, and care direction at any time.</p>
                </div>
                <div class="public-stat">
                    <span class="public-stat-label">Ambulance</span>
                    <strong>Emergency response</strong>
                    <p class="mb-0">Ambulance coordination supports urgent transfers and safer arrival for patients who need immediate care.</p>
                </div>
                <div class="public-stat">
                    <span class="public-stat-label">Doctors</span>
                    <strong>Available schedules</strong>
                    <p class="mb-0">Patients and staff can work from doctor availability before confirming appointment times.</p>
                </div>
                <div class="public-stat">
                    <span class="public-stat-label">Care team</span>
                    <strong>Organized service</strong>
                    <p class="mb-0">Reception, nurses, doctors, pharmacy, radiology, and cashier work through one coordinated system.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band public-band-soft">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-end mb-2">
                <div class="col-lg-6">
                    <p class="eyebrow">Connected workflows</p>
                    <h2 class="section-title">Built around how a busy clinic actually works</h2>
                </div>
                <div class="col-lg-6">
                    <p class="section-copy mb-0">
                        The platform links reception, doctor, pharmacy, radiology, patient, and management activities in one calm,
                        dependable workspace.
                    </p>
                </div>
            </div>

            <div class="row g-4 mt-1">
                <div class="col-lg-4">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/reception.jpg') }}" alt="Reception staff coordinating bookings">
                        <div class="media-card-body">
                            <p class="eyebrow">Reception desk</p>
                            <h3>Smart appointment coordination</h3>
                            <p class="mb-0">Book, update, and cancel visits while checking live availability before confirming each patient slot.</p>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/talk-to-doctor.jpg') }}" alt="Doctor consulting a patient">
                        <div class="media-card-body">
                            <p class="eyebrow">Doctor workspace</p>
                            <h3>Fast access to medical context</h3>
                            <p class="mb-0">Doctors can open assigned appointments, review treatment history, and capture consultation notes in one place.</p>
                        </div>
                    </article>
                </div>
                <div class="col-lg-4">
                    <article class="media-card h-100">
                        <img src="{{ asset('images/your-report.jpg') }}" alt="Management reporting and analytics">
                        <div class="media-card-body">
                            <p class="eyebrow">Management</p>
                            <h3>Clear reporting and oversight</h3>
                            <p class="mb-0">Track doctor workloads, daily visits, service coverage, and attendance trends from a centralized dashboard.</p>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="row g-4 align-items-center">
                <div class="col-xl-5">
                    <div class="showcase-grid">
                        <div class="showcase-card showcase-card-lg">
                            <img src="{{ asset('images/patient-care.jpg') }}" alt="Patient care support at CityCare">
                        </div>
                        <div class="showcase-card showcase-card-admin">
                            <img src="{{ asset('images/jonathan-admin-portrait.png') }}" alt="Jonathan Mugume, CityCare administrator">
                            <div class="showcase-card-body">
                                <p class="eyebrow">Clinic leadership</p>
                                <h3>Administrator oversight</h3>
                                <p class="mb-0">CityCare pairs patient experience with visible administrative coordination, reporting, and day-to-day leadership.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-7">
                    <p class="eyebrow">Patient and staff experience</p>
                    <h2 class="section-title">A polished public front door with a reliable internal workspace</h2>
                    <p class="section-copy">
                        Patients get a welcoming online entry point, while staff work from a cleaner operational system
                        that reduces waiting time, missed details, and appointment confusion.
                    </p>
                    <div class="feature-points">
                        <div class="feature-point">Patient sign up linked to a personal clinic profile</div>
                        <div class="feature-point">24/7 appointment inquiries and urgent care guidance</div>
                        <div class="feature-point">Ambulance service support for emergency movement and referral coordination</div>
                        <div class="feature-point">Doctor schedules and patient context available where care decisions happen</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="public-band public-cta-band">
        <div class="container-fluid px-3 px-lg-4">
            <div class="public-cta">
                <div>
                    <p class="eyebrow text-white-50">CityCare Workspace</p>
                    <h2>Professional, organized, and ready for real clinic use.</h2>
                    <p class="mb-0">
                        Open the staff portal for clinic operations, or use patient access to request care from CityCare.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-outline-light btn-lg" href="{{ route('staff.login') }}">Staff portal</a>
                    <a class="btn btn-light btn-lg" href="{{ route('login') }}">Patient login</a>
                    <a class="btn btn-outline-light btn-lg" href="{{ route('register') }}">Create patient account</a>
                </div>
            </div>
        </div>
    </section>
@endsection
