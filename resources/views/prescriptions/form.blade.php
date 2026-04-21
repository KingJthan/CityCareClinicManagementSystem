@extends('layouts.app')

@section('title', 'Prescribe Drug | CityCare')

@section('content')
    <x-page-header :title="'Prescribe for ' . $appointment->patient->full_name" :subtitle="$appointment->doctor->display_name . ' - Pharmacy request'" />

    <div class="panel panel-pad">
        <form method="POST" action="{{ route('prescriptions.store', $appointment) }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label" for="drug_id">Drug</label>
                <select class="form-select" id="drug_id" name="drug_id" required>
                    <option value="">Select drug</option>
                    @foreach($drugs as $drug)
                        <option value="{{ $drug->id }}" @selected((string) old('drug_id') === (string) $drug->id)>{{ $drug->name }} {{ $drug->strength }} - {{ $drug->dosage_form }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="dosage">Dosage</label>
                <input class="form-control" id="dosage" name="dosage" value="{{ old('dosage') }}" placeholder="Example: 1 tablet" required>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="frequency">Frequency</label>
                <input class="form-control" id="frequency" name="frequency" value="{{ old('frequency') }}" placeholder="Example: twice daily">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="duration">Duration</label>
                <input class="form-control" id="duration" name="duration" value="{{ old('duration') }}" placeholder="Example: 5 days">
            </div>
            <div class="col-12">
                <label class="form-label" for="instructions">Instructions</label>
                <textarea class="form-control" id="instructions" name="instructions" rows="4">{{ old('instructions') }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Send to pharmacy</button>
                <a class="btn btn-outline-secondary" href="{{ route('appointments.show', $appointment) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
