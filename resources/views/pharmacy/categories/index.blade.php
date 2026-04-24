@extends('layouts.app')

@section('title', 'Drug Categories | CityCare')

@section('content')
    <x-page-header title="Drug Categories" subtitle="Prevent duplicated pharmacy categories and keep medication groups clean.">
        <x-slot:actions>
            <a class="btn btn-dark" href="{{ workspace_route('drug-categories.create') }}">New category</a>
        </x-slot:actions>
    </x-page-header>

    <div class="panel panel-pad mb-3">
        <form class="row g-2">
            <div class="col-md-7"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search category or code"></div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">All statuses</option>
                    @foreach(['active', 'inactive'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-grid"><button class="btn btn-outline-secondary" type="submit">Filter</button></div>
        </form>
    </div>

    <div class="panel">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Drugs</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td><strong>{{ $category->code }}</strong></td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->drugs_count }}</td>
                            <td><x-status-pill :status="$category->status" /></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ workspace_route('drug-categories.edit', $category) }}">Edit</a>
                                <form class="d-inline" method="POST" action="{{ workspace_route('drug-categories.destroy', $category) }}" data-confirm="Archive this drug category?">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Archive</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No drug categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel-pad">{{ $categories->links() }}</div>
    </div>
@endsection
