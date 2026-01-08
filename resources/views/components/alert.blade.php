@props(['type' => 'info', 'dismissible' => true])

@php
$classes = [
    'success' => 'alert-success',
    'error' => 'alert-danger',
    'danger' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
];
$icons = [
    'success' => 'check-circle',
    'error' => 'exclamation-circle',
    'danger' => 'exclamation-triangle',
    'warning' => 'exclamation-triangle',
    'info' => 'info-circle',
];
@endphp

<div class="alert {{ $classes[$type] ?? 'alert-info' }} {{ $dismissible ? 'alert-dismissible fade show' : '' }}" role="alert">
    <i class="bi bi-{{ $icons[$type] ?? 'info-circle' }}"></i> {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>













