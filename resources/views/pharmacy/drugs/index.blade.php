@extends('layouts.app')

@section('title', 'Drugs | CityCare')

@section('content')
    <x-page-header title="Drugs" subtitle="Pharmacist-only medication inventory and stock tracking.">
        <x-slot:actions>
            <a class="btn btn-outline-secondary" href="{{ route('drug-categories.index') }}">Categories</a>
            <a class="btn btn-dark" href="{{ route('drugs.create') }}">New drug</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-lg-4"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search drug, generic name, strength"></div>
            <div class="col-lg-3">
                <select class="form-select" name="drug_category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('drug_category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 d-grid"><button class="btn btn-outline-secondary" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Drug</th><th>Category</th><th>Form</th><th>Stock</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($drugs as $drug)
                        <tr>
                            <td><strong>{{ $drug->name }}</strong><div class="small text-muted">{{ $drug->generic_name ?? 'No generic name' }} - {{ $drug->strength }}</div></td>
                            <td>{{ $drug->category->name }}</td>
                            <td>{{ $drug->dosage_form }}</td>
                            <td>{{ $drug->stock_quantity }} {{ $drug->unit }} <span class="small text-muted">(reorder {{ $drug->reorder_level }})</span></td>
                            <td><x-status-pill :status="$drug->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('drugs.edit', $drug) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ route('drugs.destroy', $drug) }}" data-confirm="Archive this drug?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Archive</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No drugs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $drugs->links() }}</div>
    </div>
@endsection
