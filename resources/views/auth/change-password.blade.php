@extends('layouts.app')

@section('title', 'Change Password | CityCare')

@section('content')
    <x-page-header title="Change Password" subtitle="Keep your CityCare account credentials current." />

    <div class="panel panel-pad" style="max-width: 620px;">
        <form method="POST" action="{{ workspace_route('password.update') }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-12">
                <label class="form-label" for="current_password">Current password</label>
                <input class="form-control" id="current_password" name="current_password" type="password" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password">New password</label>
                <input class="form-control" id="password" name="password" type="password" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm new password</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" required>
            </div>
            <div class="col-12">
                <button class="btn btn-dark" type="submit">Update password</button>
            </div>
        </form>
    </div>
@endsection
