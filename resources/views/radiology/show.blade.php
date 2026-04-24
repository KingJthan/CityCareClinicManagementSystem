@extends('layouts.app')

@section('title', 'Radiology Result | CityCare')

@section('content')
    <x-page-header :title="$order->patient->full_name . ' Radiology Order'" :subtitle="$order->study_type">
        <x-slot:actions>
            <a class="btn btn-dark" href="{{ workspace_route('radiology-orders.edit', $order) }}">Update result</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad">
        <dl class="row mb-0">
            <dt class="col-sm-3">Patient</dt><dd class="col-sm-9">{{ $order->patient->full_name }}</dd>
            <dt class="col-sm-3">Doctor</dt><dd class="col-sm-9">{{ $order->doctor->display_name }}</dd>
            <dt class="col-sm-3">Study type</dt><dd class="col-sm-9">{{ $order->study_type }}</dd>
            <dt class="col-sm-3">Priority</dt><dd class="col-sm-9">{{ ucfirst($order->priority) }}</dd>
            <dt class="col-sm-3">Clinical notes</dt><dd class="col-sm-9">{{ $order->clinical_notes }}</dd>
            <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><x-status-pill :status="$order->status" /></dd>
            <dt class="col-sm-3">Result notes</dt><dd class="col-sm-9">{{ $order->result_notes ?? 'No result entered yet.' }}</dd>
            <dt class="col-sm-3">Completed at</dt><dd class="col-sm-9">{{ $order->resulted_at?->format('M d, Y H:i') ?? 'Not completed' }}</dd>
        </dl>
    </div>
@endsection
