@props(['status'])

@php
    $map = [
        'active' => 'success',
        'scheduled' => 'primary',
        'available' => 'info',
        'checked_in' => 'info',
        'completed' => 'success',
        'paid' => 'success',
        'pending' => 'warning',
        'requested' => 'primary',
        'in_progress' => 'info',
        'cancelled' => 'secondary',
        'inactive' => 'secondary',
        'waived' => 'info',
        'refunded' => 'danger',
    ];
    $tone = $map[$status] ?? 'secondary';
@endphp

<span class="badge rounded-pill text-bg-{{ $tone }}">{{ str_replace('_', ' ', ucfirst($status)) }}</span>
