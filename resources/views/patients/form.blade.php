@extends('layouts.app')

@php($editing = $patient->exists)
@section('title', ($editing ? 'Edit' : 'New') . ' Patient | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Patient' : 'New Patient'" subtitle="Capture patient identity, emergency contact, and login access details." />

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? route('patients.update', $patient) : route('patients.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-4">
                <label class="form-label" for="first_name">First name</label>
                <input class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="last_name">Last name</label>
                <input class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $patient->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="date_of_birth">Date of birth</label>
                <input class="form-control" id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="gender">Gender</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">Select</option>
                    @foreach(['Female', 'Male', 'Other'] as $gender)
                        <option value="{{ $gender }}" @selected(old('gender', $patient->gender) === $gender)>{{ $gender }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="phone">Phone</label>
                <input class="form-control" id="phone" name="phone" value="{{ old('phone', $patient->phone) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="email">Email</label>
                <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $patient->email) }}">
            </div>

            <div class="col-12">
                <label class="form-label" for="address">Address</label>
                <input class="form-control" id="address" name="address" value="{{ old('address', $patient->address) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="emergency_contact_name">Emergency contact name</label>
                <input class="form-control" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name', $patient->emergency_contact_name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="emergency_contact_phone">Emergency contact phone</label>
                <input class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $patient->emergency_contact_phone) }}">
            </div>
            <div class="col-12">
                <label class="form-label" for="allergies">Allergies or alerts</label>
                <textarea class="form-control" id="allergies" name="allergies" rows="3">{{ old('allergies', $patient->allergies) }}</textarea>
            </div>

            @unless($editing)
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="create_user_account" name="create_user_account" value="1" @checked(old('create_user_account'))>
                        <label class="form-check-label" for="create_user_account">Create patient login account</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password">Temporary password</label>
                    <input class="form-control" id="password" name="password" type="password">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password">
                </div>
            @endunless

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">{{ $editing ? 'Save changes' : 'Create patient' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('patients.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
