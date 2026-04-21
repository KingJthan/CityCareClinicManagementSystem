@props(['title', 'subtitle' => null])

<div class="page-header">
    <div>
        <p class="eyebrow mb-1">CityCare Workspace</p>
        <h1 class="h3 mb-1">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</div>
