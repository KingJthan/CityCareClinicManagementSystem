@extends('layouts.app')

@section('title', (($portal ?? 'patient') === 'staff' ? 'Staff Login' : 'Patient Login') . ' | CityCare')

@section('content')
    @php
        $isStaffPortal = ($portal ?? 'patient') === 'staff';
        $heroImage = $isStaffPortal ? asset('images/doctor-team.jpg') : asset('images/patient-care.jpg');
        $heroTitle = $isStaffPortal ? 'Secure staff workspace access' : 'Patient access made simple';
        $heroCopy = $isStaffPortal
            ? 'Sign in to manage appointments, records, billing, pharmacy, radiology, and clinical workflows in one coordinated workspace.'
            : 'Sign in to request appointments, view your clinic information, review bills, and access your medical updates in one place.';
    @endphp

    <section class="auth-stage">
        <div class="auth-layout-card">
            <div class="auth-layout-grid">
                <div class="auth-form-shell">
                    <p class="eyebrow mb-2">{{ $isStaffPortal ? 'Staff access' : 'Patient access' }}</p>
                    <h1 class="auth-title">{{ $isStaffPortal ? 'Staff portal login' : 'Patient portal login' }}</h1>
                    <p class="auth-copy">{{ $isStaffPortal ? 'Use your assigned staff role to enter the CityCare workspace.' : 'Use your patient account to access appointments, reports, and billing.' }}</p>

                    <form method="POST" action="{{ route('login.store') }}" class="row g-3">
                        @csrf
                        @if($isStaffPortal)
                            <div class="col-12">
                                <label class="form-label" for="expected_role">Staff role</label>
                                <select class="form-select" id="expected_role" name="expected_role" required>
                                    <option value="">Select staff role</option>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" @selected(old('expected_role') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="expected_role" value="patient">
                        @endif

                        <div class="col-12">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control" id="password" name="password" type="password" required>
                        </div>

                        <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="form-check">
                                <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <div class="small text-muted">Demo password: <strong>citycare456</strong></div>
                        </div>

                        <div class="col-12 d-grid gap-2">
                            <button class="btn btn-dark btn-lg" type="submit">Login</button>
                            @if($isStaffPortal)
                                <a class="btn btn-outline-secondary" href="{{ route('staff.register') }}">Register staff account</a>
                            @else
                                <a class="btn btn-outline-secondary" href="{{ route('register') }}">Create patient account</a>
                            @endif
                        </div>
                    </form>

                    <div class="auth-meta-grid mt-4">
                        <div class="auth-meta-card">
                            <span class="auth-meta-label">Portal</span>
                            <strong>{{ $isStaffPortal ? 'Staff workspace' : 'Patient portal' }}</strong>
                            <p class="mb-0">{{ $isStaffPortal ? 'Role-based login with OTP and verified access.' : 'Appointments, bills, and records in one account.' }}</p>
                        </div>
                        <div class="auth-meta-card">
                            <span class="auth-meta-label">Testing</span>
                            <strong>{{ $isStaffPortal ? 'Staff demo emails' : 'Patient demo email' }}</strong>
                            <p class="mb-0">
                                @if($isStaffPortal)
                                    admin@citycare.test, reception@citycare.test, cashier@citycare.test, doctor.grace@citycare.test
                                @else
                                    patient@citycare.test
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <aside class="auth-visual-shell" style="background-image: linear-gradient(180deg, rgba(8, 40, 50, .24), rgba(8, 40, 50, .86)), url('{{ $heroImage }}');">
                    <div class="auth-visual-copy">
                        <span class="auth-visual-tag">{{ $isStaffPortal ? 'CityCare staff portal' : 'CityCare patient portal' }}</span>
                        <h2>{{ $heroTitle }}</h2>
                        <p>{{ $heroCopy }}</p>
                    </div>
                    <div class="auth-visual-points">
                        <div class="auth-point">
                            <strong>{{ $isStaffPortal ? 'Role checks' : 'Fast appointment access' }}</strong>
                            <span>{{ $isStaffPortal ? 'Only the right workspace opens for the selected staff role.' : 'Patients can reach appointments, medical updates, and billing quickly.' }}</span>
                        </div>
                        <div class="auth-point">
                            <strong>{{ $isStaffPortal ? 'Email verification and OTP' : 'Verified patient access' }}</strong>
                            <span>{{ $isStaffPortal ? 'Normal logins remain protected with verification and one-time codes.' : 'New patient accounts verify email before entering the system.' }}</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
