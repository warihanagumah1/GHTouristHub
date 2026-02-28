@props(['variant' => 'primary'])

@php
    $variantClasses = match ($variant) {
        'secondary' => 'fc-badge-secondary',
        'tertiary' => 'fc-badge-tertiary',
        default => 'fc-badge-primary',
    };
@endphp

<span {{ $attributes->merge(['class' => "fc-badge {$variantClasses}"]) }}>
    {{ $slot }}
</span>
