@props(['label', 'value', 'tone' => 'primary', 'icon' => 'ST', 'note' => null])

<div class="metric-card tone-{{ $tone }}">
    <div class="metric-topline">
        <div class="metric-icon">{{ $icon }}</div>
        <div class="metric-status">Live</div>
    </div>
    <div class="small text-muted">{{ $label }}</div>
    <div class="metric-value">{{ $value }}</div>
    @if($note)
        <div class="metric-note">{{ $note }}</div>
    @endif
</div>
