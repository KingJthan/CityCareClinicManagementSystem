@extends('layouts.app')

@php($editing = $category->exists)
@section('title', ($editing ? 'Edit' : 'New') . ' Drug Category | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Drug Category' : 'New Drug Category'" subtitle="Category names and codes must be unique." />

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? workspace_route('drug-categories.update', $category) : workspace_route('drug-categories.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-6">
                <label class="form-label" for="name">Category name</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="code">Code</label>
                <input class="form-control" id="code" name="code" value="{{ old('code', $category->code) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $category->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Save category</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('drug-categories.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
