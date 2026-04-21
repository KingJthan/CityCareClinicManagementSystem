@extends('layouts.app')

@php($editing = $payment->exists)
@section('title', ($editing ? 'Edit' : 'Record') . ' Billing | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Billing Record' : 'Record Billing Payment'" subtitle="Capture cashier transactions for approved appointments and patient bills." />

    <div class="alert alert-light border">
        Only cashier and administrator accounts can record payments. Reception handles appointments, not patient billing.
    </div>

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? route('payments.update', $payment) : route('payments.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label" for="appointment_id">Appointment</label>
                <select class="form-select" id="appointment_id" name="appointment_id">
                    <option value="">No appointment link</option>
                    @foreach($appointments as $appointment)
                        <option value="{{ $appointment->id }}" @selected((string) old('appointment_id', $payment->appointment_id) === (string) $appointment->id)>
                            {{ $appointment->appointment_date->format('M d') }} - {{ $appointment->patient->full_name }} with {{ $appointment->doctor->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="patient_id">Patient</label>
                <select class="form-select" id="patient_id" name="patient_id">
                    <option value="">Use appointment patient</option>
                    @foreach($patients as $patient)
                        <option value="{{ $patient->id }}" @selected((string) old('patient_id', $payment->patient_id) === (string) $patient->id)>{{ $patient->patient_number }} - {{ $patient->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label" for="amount">Amount</label>
                <input class="form-control" id="amount" name="amount" type="number" min="0" step="0.01" value="{{ old('amount', $payment->amount) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="payment_method">Method</label>
                <select class="form-select" id="payment_method" name="payment_method" required>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}" @selected(old('payment_method', $payment->payment_method ?? 'Cash') === $method)>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach($paymentStatuses as $status)
                        <option value="{{ $status }}" @selected(old('status', $payment->status ?? 'pending') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="paid_at">Paid at</label>
                <input class="form-control" id="paid_at" name="paid_at" type="datetime-local" value="{{ old('paid_at', $payment->paid_at?->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="reference">Reference</label>
                <input class="form-control" id="reference" name="reference" value="{{ old('reference', $payment->reference) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="notes">Notes</label>
                <input class="form-control" id="notes" name="notes" value="{{ old('notes', $payment->notes) }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">{{ $editing ? 'Save changes' : 'Record payment' }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('payments.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
