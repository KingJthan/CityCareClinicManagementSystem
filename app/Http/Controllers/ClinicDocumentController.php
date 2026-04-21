<?php

namespace App\Http\Controllers;

use App\Models\ClinicDocument;
use App\Models\Patient;
use App\Services\DocumentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClinicDocumentController extends Controller
{
    public function __construct(private DocumentService $documents)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $typeLabels = $this->documents->allTypeLabels();
        $patients = $this->documents->patientOptionsFor($user);
        $fixedPatient = $user->hasRole('patient') ? $user->patientProfile : null;

        $documents = $this->documents->viewableQueryFor($user)
            ->with(['patient', 'uploader'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('original_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('patient', fn ($patient) => $patient
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('patient_number', 'like', "%{$search}%"));
                });
            })
            ->when($request->document_type, fn ($query, $type) => $query->where('document_type', $type))
            ->when($request->patient_id, fn ($query, $patientId) => $query->where('patient_id', $patientId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('documents.index', [
            'allDocumentTypes' => $typeLabels,
            'documentPatient' => $fixedPatient,
            'documentTypes' => $this->documents->typeOptionsFor($user),
            'documents' => $documents,
            'patients' => $patients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $typeOptions = $this->documents->typeOptionsFor($user);

        $data = $request->validate([
            'patient_id' => ['nullable', 'integer', 'exists:patients,id'],
            'document_type' => ['required', Rule::in(array_keys($typeOptions))],
            'title' => ['required', 'string', 'max:160'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
        ]);

        $patient = filled($data['patient_id'] ?? null) ? Patient::findOrFail($data['patient_id']) : null;

        abort_unless($this->documents->canUploadForPatient($user, $patient), 403);

        $file = $request->file('document');
        $path = $file->store('clinic-documents/' . now()->format('Y/m'), 'local');

        ClinicDocument::create([
            'patient_id' => $patient?->id,
            'owner_user_id' => $patient ? $patient->user_id : $user->id,
            'uploaded_by' => $user->id,
            'document_type' => $data['document_type'],
            'title' => $data['title'],
            'notes' => $data['notes'] ?? null,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize() ?: 0,
            'status' => 'active',
        ]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download(Request $request, ClinicDocument $document): StreamedResponse
    {
        abort_unless($this->documents->canView($request->user(), $document), 403);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        return Storage::disk($document->disk)->download($document->path, $document->original_name);
    }

    public function destroy(Request $request, ClinicDocument $document): RedirectResponse
    {
        abort_unless($this->documents->canDelete($request->user(), $document), 403);

        Storage::disk($document->disk)->delete($document->path);
        $document->delete();

        return back()->with('success', 'Document removed.');
    }
}
