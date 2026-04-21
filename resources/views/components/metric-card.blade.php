@props(['label', 'value', 'tone' => 'primary'])

<div class="metric-card tone-{{ $tone }}">
    <div class="small text-muted">{{ $label }}</div>
    <div class="metric-value">{{ $value }}</div>
</div>
