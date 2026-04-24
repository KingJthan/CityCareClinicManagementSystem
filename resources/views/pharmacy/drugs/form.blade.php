@extends('layouts.app')

@php($editing = $drug->exists)
@section('title', ($editing ? 'Edit' : 'New') . ' Drug | CityCare')

@section('content')
    <x-page-header :title="$editing ? 'Edit Drug' : 'New Drug'" subtitle="Drug name, strength, and dosage form cannot be duplicated." />

    <div class="panel panel-pad">
        <form method="POST" action="{{ $editing ? workspace_route('drugs.update', $drug) : workspace_route('drugs.store') }}" class="row g-3">
            @csrf
            @if($editing)
                @method('PUT')
            @endif

            <div class="col-md-4">
                <label class="form-label" for="drug_category_id">Category</label>
                <select class="form-select" id="drug_category_id" name="drug_category_id" required>
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('drug_category_id', $drug->drug_category_id) === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="name">Drug name</label>
                <input class="form-control" id="name" name="name" value="{{ old('name', $drug->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="generic_name">Generic name</label>
                <input class="form-control" id="generic_name" name="generic_name" value="{{ old('generic_name', $drug->generic_name) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="strength">Strength</label>
                <input class="form-control" id="strength" name="strength" value="{{ old('strength', $drug->strength) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="dosage_form">Dosage form</label>
                <input class="form-control" id="dosage_form" name="dosage_form" value="{{ old('dosage_form', $drug->dosage_form) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="unit">Unit</label>
                <input class="form-control" id="unit" name="unit" value="{{ old('unit', $drug->unit ?? 'tablet') }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="stock_quantity">Stock</label>
                <input class="form-control" id="stock_quantity" name="stock_quantity" type="number" min="0" value="{{ old('stock_quantity', $drug->stock_quantity ?? 0) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="reorder_level">Reorder</label>
                <input class="form-control" id="reorder_level" name="reorder_level" type="number" min="0" value="{{ old('reorder_level', $drug->reorder_level ?? 10) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="status">Status</label>
                <select class="form-select" id="status" name="status" required>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $drug->status ?? 'active') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-dark" type="submit">Save drug</button>
                <a class="btn btn-outline-secondary" href="{{ workspace_route('drugs.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
