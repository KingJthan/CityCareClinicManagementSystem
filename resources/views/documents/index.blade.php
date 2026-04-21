@extends('layouts.app')

@section('title', 'Documents | CityCare')

@section('content')
    <x-page-header title="Documents" subtitle="Upload and review role-appropriate clinic documents securely." />

    <div class="row g-4 mb-4">
        <div class="col-xl-4">
            @include('documents._upload-panel', [
                'documentPatient' => $documentPatient,
                'documentTypes' => $documentTypes,
                'patients' => $patients,
            ])
        </div>
        <div class="col-xl-8">
            <div class="panel panel-pad h-100">
                <h2 class="h5 mb-3">Filter documents</h2>
                <form class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="search">Search</label>
                        <input class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Title, file name, notes, or patient">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="document_type_filter">Type</label>
                        <select class="form-select" id="document_type_filter" name="document_type">
                            <option value="">All types</option>
                            @foreach($allDocumentTypes as $value => $label)
                                <option value="{{ $value }}" @selected(request('document_type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!$documentPatient && $patients->isNotEmpty())
                        <div class="col-md-3">
                            <label class="form-label" for="patient_id_filter">Patient</label>
                            <select class="form-select" id="patient_id_filter" name="patient_id">
                                <option value="">All patients</option>
                                @foreach($patients as $patientOption)
                                    <option value="{{ $patientOption->id }}" @selected((string) request('patient_id') === (string) $patientOption->id)>
                                        {{ $patientOption->patient_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-12">
                        <button class="btn btn-outline-secondary" type="submit">Apply filters</button>
                        <a class="btn btn-outline-secondary" href="{{ route('documents.index') }}">Clear</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('documents._list', [
        'allDocumentTypes' => $allDocumentTypes,
        'documents' => $documents,
        'title' => 'Uploaded documents',
        'subtitle' => 'Patient documents, staff documents, and role-specific attachments are shown according to access permissions.',
    ])
@endsection
