@extends('layouts.app')

@section('title', (($portal ?? 'patient') === 'staff' ? 'Staff Login' : 'Patient Login') . ' | CityCare')

@section('content')
    <div class="auth-wrap">
        <div class="panel panel-pad auth-card">
            <p class="eyebrow mb-1">Secure access</p>
            <h1 class="h3 mb-3">{{ ($portal ?? 'patient') === 'staff' ? 'Staff portal login' : 'Patient portal login' }}</h1>

            <form method="POST" action="{{ route('login.store') }}" class="row g-3">
                @csrf
                @if(($portal ?? 'patient') === 'staff')
                    <div class="col-12">
                        <label class="form-label" for="expected_role">Staff portal</label>
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
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div class="form-check">
                        <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    @if(($portal ?? 'patient') !== 'staff')
                        <a href="{{ route('register') }}">New patient?</a>
                    @endif
                </div>
                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">Login</button>
                </div>
            </form>

            <hr>
            <div class="small text-muted">
                Demo users use <strong>citycare456</strong> and open without OTP.
                @if(($portal ?? 'patient') === 'staff')
                    Try admin@citycare.test, reception@citycare.test, cashier@citycare.test, pharmacist@citycare.test, radiology@citycare.test, or doctor.grace@citycare.test.
                @else
                    Try patient@citycare.test.
                @endif
            </div>
        </div>
    </div>
@endsection
