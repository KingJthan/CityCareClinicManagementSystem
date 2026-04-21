@extends('layouts.app')

@section('title', 'Radiology Order | CityCare')

@section('content')
    <x-page-header :title="'Radiology for ' . $appointment->patient->full_name" :subtitle="$appointment->doctor->display_name . ' - Imaging request'" />

    <div class="panel panel-pad">
        <form method="POST" action="{{ route('radiology-orders.store', $appointment) }}" class="row g-3">
            @csrf
            <div class="col-md-7">
                <label class="form-label" for="study_type">Study type</label>
                <input class="form-control" id="study_type" name="study_type" value="{{ old('study_type') }}" placeholder="Example: Chest X-ray, CT head, ultrasound abdomen" required>
            </div>
            <div class="col-md-5">
                <label class="form-label" for="priority">Priority</label>
                <select class="form-select" id="priority" name="priority" required>
                    @foreach(['routine', 'urgent', 'stat'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', 'routine') === $priority)>{{ ucfirst($priority) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="clinical_notes">Clinical notes</label>
                <textarea class="form-control" id="clinical_notes" name="clinical_notes" rows="5" required>{{ old('clinical_notes') }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Send to radiology</button>
                <a class="btn btn-outline-secondary" href="{{ route('appointments.show', $appointment) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
