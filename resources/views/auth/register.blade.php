@extends('layouts.app')

@section('title', 'Patient Registration | CityCare')

@section('content')
    <div class="auth-wrap">
        <div class="panel panel-pad auth-card">
            <p class="eyebrow mb-1">Patient access</p>
            <h1 class="h3 mb-3">Create patient account</h1>
            <p class="text-muted">A verification code will be sent to your email before the account opens.</p>

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
                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">Create account</button>
                </div>
            </form>
        </div>
    </div>
@endsection
