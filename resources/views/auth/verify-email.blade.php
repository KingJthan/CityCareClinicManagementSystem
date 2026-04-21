@extends('layouts.app')

@section('title', 'Verify Email | CityCare')

@section('content')
    <div class="auth-wrap">
        <div class="panel panel-pad auth-card">
            <p class="eyebrow mb-1">Email verification</p>
            <h1 class="h3 mb-3">Confirm your email</h1>
            <p class="text-muted">Enter the 6-digit code sent to {{ $email }}.</p>

            <form method="POST" action="{{ route('verification.verify') }}" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label" for="code">Verification code</label>
                    <input class="form-control" id="code" name="code" inputmode="numeric" maxlength="6" value="{{ old('code') }}" required autofocus>
                </div>
                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">Verify email</button>
                </div>
            </form>

            <form method="POST" action="{{ route('verification.resend') }}" class="mt-3">
                @csrf
                <button class="btn btn-outline-secondary w-100" type="submit">Send a new code</button>
            </form>
        </div>
    </div>
@endsection
