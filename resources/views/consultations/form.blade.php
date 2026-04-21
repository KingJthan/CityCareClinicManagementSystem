@extends('layouts.app')

@section('title', 'Consultation Notes | CityCare')

@section('content')
    <x-page-header :title="'Consultation for ' . $appointment->patient->full_name" :subtitle="$appointment->doctor->display_name . ' - ' . $appointment->appointment_date->format('M d, Y')" />

    <div class="panel panel-pad">
        <form method="POST" action="{{ route('consultations.update', $appointment) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-6">
                <label class="form-label" for="symptoms">Symptoms</label>
                <textarea class="form-control" id="symptoms" name="symptoms" rows="5">{{ old('symptoms', $appointment->consultation?->symptoms) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="diagnosis">Diagnosis</label>
                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="5" required>{{ old('diagnosis', $appointment->consultation?->diagnosis) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="treatment_plan">Treatment plan</label>
                <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="5" required>{{ old('treatment_plan', $appointment->consultation?->treatment_plan) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="prescription">Prescription</label>
                <textarea class="form-control" id="prescription" name="prescription" rows="5">{{ old('prescription', $appointment->consultation?->prescription) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="next_visit_date">Next visit date</label>
                <input class="form-control" id="next_visit_date" name="next_visit_date" type="date" value="{{ old('next_visit_date', $appointment->consultation?->next_visit_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Save consultation</button>
                <a class="btn btn-outline-secondary" href="{{ route('appointments.show', $appointment) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
