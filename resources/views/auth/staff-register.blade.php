@extends('layouts.app')

@section('title', 'Staff Registration | CityCare')

@section('content')
    <section class="auth-stage">
        <div class="auth-layout-card">
            <div class="auth-layout-grid">
                <div class="auth-form-shell">
                    <p class="eyebrow mb-2">Staff onboarding</p>
                    <h1 class="auth-title">Register staff account</h1>
                    <p class="auth-copy">Create a staff login for operational roles. Doctor registrations also create a starter doctor profile for scheduling and dashboard access.</p>

                    <form method="POST" action="{{ route('staff.register.store') }}" class="row g-3">
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
                            <input class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="role">Staff role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select role</option>
                                @foreach($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="department_id">Department</label>
                            <select class="form-select" id="department_id" name="department_id">
                                <option value="">Select department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Required when registering a doctor account.</div>
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
                            <button class="btn btn-dark btn-lg" type="submit">Register staff account</button>
                            <a class="btn btn-outline-secondary" href="{{ route('staff.login') }}">Back to staff login</a>
                        </div>
                    </form>
                </div>

                <aside class="auth-visual-shell" style="background-image: linear-gradient(180deg, rgba(8, 40, 50, .24), rgba(8, 40, 50, .86)), url('{{ asset('images/team-happy.jpg') }}');">
                    <div class="auth-visual-copy">
                        <span class="auth-visual-tag">CityCare workspace</span>
                        <h2>Professional staff access with role-based entry</h2>
                        <p>Reception, billing, pharmacy, radiology, nursing, and doctor workflows stay separated while still working from one coordinated system.</p>
                    </div>
                    <div class="auth-visual-points">
                        <div class="auth-point">
                            <strong>Role-specific dashboard</strong>
                            <span>Each staff role lands in a workspace designed around assigned duties and reports.</span>
                        </div>
                        <div class="auth-point">
                            <strong>Secure login flow</strong>
                            <span>Staff accounts verify email and continue to OTP-protected login for regular access.</span>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
