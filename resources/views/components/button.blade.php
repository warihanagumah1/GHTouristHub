@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $variantClasses = match ($variant) {
        'danger' => 'fc-btn-danger',
        'secondary' => 'fc-btn-secondary',
        'tertiary' => 'fc-btn-tertiary',
        'outline' => 'fc-btn-outline',
        'ghost' => 'fc-btn-ghost',
        default => 'fc-btn-primary',
    };
@endphp

<button {{ $attributes->merge(['type' => $type, 'class' => "fc-btn {$variantClasses}"]) }}>
    {{ $slot }}
</button>
