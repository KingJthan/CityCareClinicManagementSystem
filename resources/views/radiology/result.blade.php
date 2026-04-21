@extends('layouts.app')

@section('title', 'Update Radiology | CityCare')

@section('content')
    <x-page-header :title="'Update ' . $order->study_type" :subtitle="$order->patient->full_name . ' - ' . $order->doctor->display_name" />

    <div class="panel panel-pad">
        <form method="POST" action="{{ route('radiology-orders.update', $order) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['requested', 'in_progress', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $order->status) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="result_notes">Result notes</label>
                <textarea class="form-control" id="result_notes" name="result_notes" rows="5">{{ old('result_notes', $order->result_notes) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Save radiology update</button>
                <a class="btn btn-outline-secondary" href="{{ route('radiology-orders.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
