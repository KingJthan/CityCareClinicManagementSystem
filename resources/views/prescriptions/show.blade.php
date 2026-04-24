@extends('layouts.app')

@section('title', 'Prescription | CityCare')

@section('content')
    <x-page-header :title="$prescription->patient->full_name . ' Prescription'" :subtitle="$prescription->drug->name . ' ' . $prescription->drug->strength">
        <x-slot:actions>
            <a class="btn btn-dark" href="{{ workspace_route('prescriptions.edit', $prescription) }}">Process</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad">
        <dl class="row mb-0">
            <dt class="col-sm-3">Patient</dt><dd class="col-sm-9">{{ $prescription->patient->full_name }}</dd>
            <dt class="col-sm-3">Doctor</dt><dd class="col-sm-9">{{ $prescription->doctor->display_name }}</dd>
            <dt class="col-sm-3">Drug</dt><dd class="col-sm-9">{{ $prescription->drug->name }} {{ $prescription->drug->strength }}</dd>
            <dt class="col-sm-3">Dosage</dt><dd class="col-sm-9">{{ $prescription->dosage }}</dd>
            <dt class="col-sm-3">Frequency</dt><dd class="col-sm-9">{{ $prescription->frequency ?? 'Not specified' }}</dd>
            <dt class="col-sm-3">Duration</dt><dd class="col-sm-9">{{ $prescription->duration ?? 'Not specified' }}</dd>
            <dt class="col-sm-3">Instructions</dt><dd class="col-sm-9">{{ $prescription->instructions ?? 'None' }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><x-status-pill :status="$prescription->status" /></dd>
            <dt class="col-sm-3">Pharmacist notes</dt><dd class="col-sm-9">{{ $prescription->pharmacist_notes ?? 'None' }}</dd>
        </dl>
    </div>
@endsection
