@props(['variant' => 'info'])

@php
    $variantClasses = match ($variant) {
        'success' => 'fc-alert-success',
        'warning' => 'fc-alert-warning',
        'danger' => 'fc-alert-danger',
        default => 'fc-alert-info',
    };
@endphp

<div role="alert" {{ $attributes->merge(['class' => "fc-alert {$variantClasses}"]) }}>
    {{ $slot }}
</div>
