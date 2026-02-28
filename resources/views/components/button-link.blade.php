@props([
    'href' => '#',
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

<a {{ $attributes->merge(['href' => $href, 'class' => "fc-btn {$variantClasses}"]) }}>
    {{ $slot }}
</a>
