@extends('layouts.app')

@section('title', $department->name . ' | CityCare')

@section('content')
    <x-page-header :title="$department->name" subtitle="Department profile and assigned doctor roster.">
        <x-slot:actions>
            <a class="btn btn-outline-secondary" href="{{ route('departments.edit', $department) }}">Edit</a>
            <form method="POST" action="{{ route('departments.destroy', $department) }}" data-confirm="Archive this department?">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger" type="submit">Archive</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="panel panel-pad h-100">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Code</dt><dd class="col-sm-8">{{ $department->code }}</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $department->location ?? 'Not set' }}</dd>
                    <dt class="col-sm-4">Status</dt><dd class="col-sm-8"><x-status-pill :status="$department->status" /></dd>
                    <dt class="col-sm-4">Appointments</dt><dd class="col-sm-8">{{ $department->appointments_count }}</dd>
                    <dt class="col-sm-4">Description</dt><dd class="col-sm-8">{{ $department->description ?? 'No description added.' }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-pad border-bottom">
                    <h2 class="h5 mb-0">Doctors in this department</h2>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead><tr><th>Doctor</th><th>Specialization</th><th>Room</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($department->doctors as $doctor)
                                <tr>
                                    <td><a href="{{ route('doctors.show', $doctor) }}">{{ $doctor->display_name }}</a></td>
                                    <td>{{ $doctor->specialization }}</td>
                                    <td>{{ $doctor->room ?? 'Not set' }}</td>
                                    <td><x-status-pill :status="$doctor->status" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No doctors assigned yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
