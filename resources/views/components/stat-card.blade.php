@props([
    'label',
    'value',
    'trend' => null,
    'trendDirection' => 'neutral',
])

@php
    $trendClass = match ($trendDirection) {
        'up' => 'text-emerald-700',
        'down' => 'text-secondary',
        default => 'text-primary/70',
    };
@endphp

<x-card {{ $attributes }}>
    <p class="text-xs font-semibold uppercase tracking-wider text-primary/65">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-primary">{{ $value }}</p>
    @if ($trend)
        <p class="mt-2 text-sm {{ $trendClass }}">{{ $trend }}</p>
    @endif
</x-card>
