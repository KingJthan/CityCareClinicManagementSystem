@extends('layouts.app')

@section('title', 'Process Prescription | CityCare')

@section('content')
    <x-page-header :title="'Process ' . $prescription->drug->name" :subtitle="$prescription->patient->full_name . ' - ' . $prescription->doctor->display_name" />

    <div class="panel panel-pad">
        <form method="POST" action="{{ workspace_route('prescriptions.update', $prescription) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['pending', 'dispensed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $prescription->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="pharmacist_notes">Pharmacist notes</label>
                <textarea class="form-control" id="pharmacist_notes" name="pharmacist_notes" rows="4">{{ old('pharmacist_notes', $prescription->pharmacist_notes) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Save pharmacy update</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('prescriptions.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
