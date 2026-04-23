@extends('layouts.app')

@section('title', 'Patient Registration | CityCare')

@section('content')
    <section class="auth-stage">
        <div class="auth-layout-card">
            <div class="auth-layout-grid">
                <div class="auth-form-shell">
                    <p class="eyebrow mb-2">Patient access</p>
                    <h1 class="auth-title">Create patient account</h1>
                    <p class="auth-copy">Set up your patient login, then verify your email before opening the CityCare portal.</p>

                    <form method="POST" action="{{ route('register.store') }}" class="row g-3">
                        @csrf
                        <div class="col-12">
                            <label class="form-label" for="name">Full name</label>
                            <input class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="phone">Phone</label>
                            <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" placeholder="At least 10 digits">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="date_of_birth">Date of birth</label>
                            <input class="form-control" id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="gender">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select</option>
                                @foreach(['Female', 'Male', 'Other'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender') === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="address">Address</label>
                            <input class="form-control" id="address" name="address" value="{{ old('address') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="password_confirmation">Confirm password</label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
                        </div>
                        <div class="col-12 d-grid gap-2">
                            <button class="btn btn-dark btn-lg" type="submit">Create patient account</button>
                            <a class="btn btn-outline-secondary" href="{{ route('login') }}">Already have a patient account?</a>
                        </div>
                    </form>
                </div>

                <aside class="auth-visual-shell" style="background-image: linear-gradient(180deg, rgba(8, 40, 50, .26), rgba(8, 40, 50, .86)), url('{{ asset('images/talk-to-doctor.jpg') }}');">
                    <div class="auth-visual-copy">
                        <span class="auth-visual-tag">Patient registration</span>
                        <h2>One account for appointments, bills, and medical updates</h2>
                        <p>Patients can request appointments, upload key documents, review care updates, and track their clinic access in one calm, professional portal.</p>
                    </div>
                    <div class="auth-visual-points">
                        <div class="auth-point">
                            <strong>Email verification first</strong>
                            <span>New registrations must verify email before entering the patient dashboard.</span>
                        </div>
                        <div class="auth-point">
                            <strong>Prepared for care access</strong>
                            <span>Appointments, reports, insurance details, and billing stay available under one login.</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
