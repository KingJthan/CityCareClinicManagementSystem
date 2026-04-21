@php
    $fixedPatient = $documentPatient ?? null;
    $patientOptions = $patients ?? collect();
@endphp

<div class="panel panel-pad h-100">
    <h2 class="h5 mb-3">Upload document</h2>
    <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data" class="row g-3">
        @csrf

        @if($fixedPatient)
            <input type="hidden" name="patient_id" value="{{ $fixedPatient->id }}">
            <div class="col-12">
                <label class="form-label">Patient</label>
                <div class="form-control bg-light">{{ $fixedPatient->patient_number }} - {{ $fixedPatient->full_name }}</div>
            </div>
        @elseif($patientOptions->isNotEmpty())
            <div class="col-12">
                <label class="form-label" for="patient_id">Patient link</label>
                <select class="form-select" id="patient_id" name="patient_id">
                    <option value="">No patient link</option>
                    @foreach($patientOptions as $patientOption)
                        <option value="{{ $patientOption->id }}" @selected((string) old('patient_id', request('patient_id')) === (string) $patientOption->id)>
                            {{ $patientOption->patient_number }} - {{ $patientOption->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-6">
            <label class="form-label" for="document_type">Document type</label>
            <select class="form-select" id="document_type" name="document_type" required>
                <option value="">Select type</option>
                @foreach($documentTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('document_type') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="title">Title</label>
            <input class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="e.g. National ID front" required>
        </div>

        <div class="col-12">
            <label class="form-label" for="document">File</label>
            <input class="form-control" id="document" name="document" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
            <div class="form-text">Allowed: PDF, JPG, PNG, DOC, DOCX. Maximum size 5 MB.</div>
        </div>

        <div class="col-12">
            <label class="form-label" for="notes">Notes</label>
            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Optional context for this document">{{ old('notes') }}</textarea>
        </div>

        <div class="col-12">
            <button class="btn btn-dark" type="submit">Upload document</button>
        </div>
    </form>
</div>
