@extends('layouts.app')

@php($editing = $department->exists)
@section('title', ($editing ? 'Edit' : 'New') . ' Department | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Department' : 'New Department'" subtitle="Keep clinic department records clear and searchable." />

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? workspace_route('departments.update', $department) : workspace_route('departments.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-8">
                <label class="form-label" for="name">Department name</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $department->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="code">Code</label>
                <input class="form-control" id="code" name="code" value="{{ old('code', $department->code) }}" required>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="location">Location</label>
                <input class="form-control" id="location" name="location" value="{{ old('location', $department->location) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $department->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $department->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">{{ $editing ? 'Save changes' : 'Create department' }}</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('departments.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
