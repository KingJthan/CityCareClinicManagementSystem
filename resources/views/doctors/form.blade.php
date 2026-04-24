@extends('layouts.app')

@php
    $editing = $doctor->exists;
    $selectedDays = old('working_days', $doctor->working_days ?? [1, 2, 3, 4, 5]);
    $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 0 => 'Sun'];
@endphp
@section('title', ($editing ? 'Edit' : 'New') . ' Doctor | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Doctor' : 'New Doctor'" subtitle="Create staff login details and clinical schedule settings." />

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? workspace_route('doctors.update', $doctor) : workspace_route('doctors.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-4">
                <label class="form-label" for="name">Doctor name</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="email">Login email</label>
                <input class="form-control" id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="phone">Phone</label>
                <input class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label" for="password">{{ $editing ? 'New password' : 'Password' }}</label>
                <input class="form-control" id="password" name="password" type="password" @required(!$editing)>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="password_confirmation">Confirm password</label>
                <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" @required(!$editing)>
            </div>

            <div class="col-md-4">
                <label class="form-label" for="department_id">Department</label>
                <select class="form-select" id="department_id" name="department_id" required>
                    <option value="">Select department</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $doctor->department_id) === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="staff_number">Staff number</label>
                <input class="form-control" id="staff_number" name="staff_number" value="{{ old('staff_number', $doctor->staff_number) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="license_number">License number</label>
                <input class="form-control" id="license_number" name="license_number" value="{{ old('license_number', $doctor->license_number) }}" required>
            </div>

            <div class="col-md-5">
                <label class="form-label" for="specialization">Specialization</label>
                <input class="form-control" id="specialization" name="specialization" value="{{ old('specialization', $doctor->specialization) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="consultation_fee">Fee</label>
                <input class="form-control" id="consultation_fee" name="consultation_fee" type="number" min="0" value="{{ old('consultation_fee', $doctor->consultation_fee ?? 0) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="room">Room</label>
                <input class="form-control" id="room" name="room" value="{{ old('room', $doctor->room) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="slot_minutes">Slot minutes</label>
                <input class="form-control" id="slot_minutes" name="slot_minutes" type="number" min="15" max="120" value="{{ old('slot_minutes', $doctor->slot_minutes ?? 30) }}" required>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="shift_starts_at">Shift starts</label>
                <input class="form-control" id="shift_starts_at" name="shift_starts_at" type="time" value="{{ old('shift_starts_at', substr($doctor->shift_starts_at ?? '08:00', 0, 5)) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="shift_ends_at">Shift ends</label>
                <input class="form-control" id="shift_ends_at" name="shift_ends_at" type="time" value="{{ old('shift_ends_at', substr($doctor->shift_ends_at ?? '17:00', 0, 5)) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Profile status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $doctor->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="user_status">Login status</label>
                <select class="form-select" id="user_status" name="user_status">
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('user_status', $user->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label">Working days</label>
                <div class="d-flex flex-wrap gap-3">
                    @foreach($days as $value => $label)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="day{{ $value }}" name="working_days[]" value="{{ $value }}" @checked(in_array($value, array_map('intval', $selectedDays), true))>
                            <label class="form-check-label" for="day{{ $value }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">{{ $editing ? 'Save changes' : 'Create doctor' }}</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('doctors.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
