@props([
    'amount' => 0,
    'from' => 'USD',
    'showOriginal' => false,
    'size' => 2,
])

@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $fromCurrency = strtoupper((string) $from ?: 'USD');
    $displayCurrency = $currencyService->selectedCurrencyCode();
    $convertedAmount = $currencyService->convert((float) $amount, $fromCurrency, $displayCurrency);
@endphp

<span {{ $attributes }}>
    {{ $currencyService->symbol($displayCurrency) }}{{ number_format($convertedAmount, (int) $size) }} {{ $displayCurrency }}
    @if ($showOriginal && $displayCurrency !== $fromCurrency)
        <span class="text-xs text-primary/60">
            ({{ $currencyService->format((float) $amount, $fromCurrency) }})
        </span>
    @endif
</span>
