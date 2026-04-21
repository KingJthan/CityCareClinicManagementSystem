@php
    $typeLabels = $allDocumentTypes ?? [];
@endphp

<div class="panel h-100">
    <div class="panel-pad border-bottom d-flex flex-column flex-md-row justify-content-between gap-2">
        <div>
            <h2 class="h5 mb-1">{{ $title ?? 'Documents' }}</h2>
            <p class="text-muted small mb-0">{{ $subtitle ?? 'Uploaded documents are only available to authorized users.' }}</p>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Type</th>
                    <th>Patient</th>
                    <th>Uploaded by</th>
                    <th>Size</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td>
                            <strong>{{ $document->title }}</strong>
                            <div class="small text-muted">{{ $document->original_name }}</div>
                            @if($document->notes)
                                <div class="small text-muted">{{ $document->notes }}</div>
                            @endif
                        </td>
                        <td>{{ $typeLabels[$document->document_type] ?? str_replace('_', ' ', ucfirst($document->document_type)) }}</td>
                        <td>{{ $document->patient?->full_name ?? 'General workspace' }}</td>
                        <td>
                            {{ $document->uploader?->name ?? 'Unknown' }}
                            <div class="small text-muted">{{ $document->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td>{{ $document->size ? number_format($document->size / 1024, 1) . ' KB' : 'N/A' }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('documents.download', $document) }}">Download</a>
                                @if(auth()->user()->hasRole('admin') || $document->uploaded_by === auth()->id())
                                    <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm="Remove this uploaded document?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No documents uploaded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($documents, 'links'))
        <div class="panel-pad">{{ $documents->links() }}</div>
    @endif
</div>
