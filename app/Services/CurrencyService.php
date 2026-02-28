<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CurrencyService
{
    /**
     * Static fallback for early bootstrap and when DB is unavailable.
     *
     * @var array<string, array{name: string, symbol: string, rate_from_usd: float, country: string, flag: string}>
     */
    protected array $fallback = [
        'USD' => ['name' => 'US Dollar', 'symbol' => '$', 'rate_from_usd' => 1.0, 'country' => 'United States', 'flag' => '🇺🇸'],
        'EUR' => ['name' => 'Euro', 'symbol' => '€', 'rate_from_usd' => 0.92, 'country' => 'European Union', 'flag' => '🇪🇺'],
        'GBP' => ['name' => 'British Pound', 'symbol' => '£', 'rate_from_usd' => 0.79, 'country' => 'United Kingdom', 'flag' => '🇬🇧'],
        'JPY' => ['name' => 'Japanese Yen', 'symbol' => '¥', 'rate_from_usd' => 149.00, 'country' => 'Japan', 'flag' => '🇯🇵'],
        'CAD' => ['name' => 'Canadian Dollar', 'symbol' => 'C$', 'rate_from_usd' => 1.35, 'country' => 'Canada', 'flag' => '🇨🇦'],
        'AUD' => ['name' => 'Australian Dollar', 'symbol' => 'A$', 'rate_from_usd' => 1.52, 'country' => 'Australia', 'flag' => '🇦🇺'],
        'CHF' => ['name' => 'Swiss Franc', 'symbol' => 'CHF ', 'rate_from_usd' => 0.88, 'country' => 'Switzerland', 'flag' => '🇨🇭'],
        'CNY' => ['name' => 'Chinese Yuan', 'symbol' => '¥', 'rate_from_usd' => 7.18, 'country' => 'China', 'flag' => '🇨🇳'],
        'INR' => ['name' => 'Indian Rupee', 'symbol' => '₹', 'rate_from_usd' => 83.10, 'country' => 'India', 'flag' => '🇮🇳'],
        'AED' => ['name' => 'UAE Dirham', 'symbol' => 'AED ', 'rate_from_usd' => 3.67, 'country' => 'Dubai, UAE', 'flag' => '🇦🇪'],
        'EGP' => ['name' => 'Egyptian Pound', 'symbol' => 'E£', 'rate_from_usd' => 49.20, 'country' => 'Egypt', 'flag' => '🇪🇬'],
        'SAR' => ['name' => 'Saudi Riyal', 'symbol' => 'SAR ', 'rate_from_usd' => 3.75, 'country' => 'Saudi Arabia', 'flag' => '🇸🇦'],
        'ZAR' => ['name' => 'South African Rand', 'symbol' => 'R', 'rate_from_usd' => 18.40, 'country' => 'South Africa', 'flag' => '🇿🇦'],
        'NZD' => ['name' => 'New Zealand Dollar', 'symbol' => 'NZ$', 'rate_from_usd' => 1.64, 'country' => 'New Zealand', 'flag' => '🇳🇿'],
        'SEK' => ['name' => 'Swedish Krona', 'symbol' => 'SEK ', 'rate_from_usd' => 10.40, 'country' => 'Sweden', 'flag' => '🇸🇪'],
        'NOK' => ['name' => 'Norwegian Krone', 'symbol' => 'NOK ', 'rate_from_usd' => 10.70, 'country' => 'Norway', 'flag' => '🇳🇴'],
        'DKK' => ['name' => 'Danish Krone', 'symbol' => 'DKK ', 'rate_from_usd' => 6.85, 'country' => 'Denmark', 'flag' => '🇩🇰'],
        'SGD' => ['name' => 'Singapore Dollar', 'symbol' => 'S$', 'rate_from_usd' => 1.35, 'country' => 'Singapore', 'flag' => '🇸🇬'],
        'HKD' => ['name' => 'Hong Kong Dollar', 'symbol' => 'HK$', 'rate_from_usd' => 7.81, 'country' => 'Hong Kong', 'flag' => '🇭🇰'],
        'BRL' => ['name' => 'Brazilian Real', 'symbol' => 'R$', 'rate_from_usd' => 5.10, 'country' => 'Brazil', 'flag' => '🇧🇷'],
        'MXN' => ['name' => 'Mexican Peso', 'symbol' => 'MX$', 'rate_from_usd' => 17.10, 'country' => 'Mexico', 'flag' => '🇲🇽'],
        'GHS' => ['name' => 'Ghanaian Cedi', 'symbol' => 'GHc ', 'rate_from_usd' => 15.20, 'country' => 'Ghana', 'flag' => '🇬🇭'],
        'KES' => ['name' => 'Kenyan Shilling', 'symbol' => 'KSh', 'rate_from_usd' => 130.00, 'country' => 'Kenya', 'flag' => '🇰🇪'],
        'NGN' => ['name' => 'Nigerian Naira', 'symbol' => 'NGN ', 'rate_from_usd' => 1580.00, 'country' => 'Nigeria', 'flag' => '🇳🇬'],
    ];

    /**
     * Get selectable currencies.
     */
    public function activeCurrencies(): Collection
    {
        if (! $this->canUseCurrenciesTable()) {
            return collect($this->fallback)->map(function (array $currency, string $code): object {
                return (object) [
                    'code' => $code,
                    'name' => $currency['name'],
                    'symbol' => $currency['symbol'],
                    'rate_from_usd' => $currency['rate_from_usd'],
                ];
            })->sortBy('code')->values();
        }

        return Currency::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['code', 'name', 'symbol', 'rate_from_usd']);
    }

    /**
     * Common quick-select currencies for dropdown top section.
     *
     * @return array<int, string>
     */
    public function popularCurrencyCodes(): array
    {
        return ['USD', 'EUR', 'GBP', 'AED', 'EGP', 'JPY', 'CAD', 'AUD', 'GHS', 'KES'];
    }

    /**
     * Metadata used by UI currency selector.
     *
     * @return array{country: string, flag: string}
     */
    public function meta(string $currencyCode): array
    {
        $code = strtoupper($currencyCode ?: 'USD');

        return [
            'country' => (string) ($this->fallback[$code]['country'] ?? 'Global'),
            'flag' => (string) ($this->fallback[$code]['flag'] ?? '🌐'),
        ];
    }

    /**
     * Get currently selected display currency code.
     */
    public function selectedCurrencyCode(): string
    {
        $default = $this->defaultCurrencyCode();

        if (! app()->bound('session') || app()->runningInConsole()) {
            return $default;
        }

        $selected = strtoupper((string) session('market_currency', $default));

        if (! $this->isSupportedCurrency($selected)) {
            return $default;
        }

        return $selected;
    }

    /**
     * Persist selected display currency code.
     */
    public function setSelectedCurrency(string $code): void
    {
        if (! app()->bound('session')) {
            return;
        }

        $normalized = strtoupper(trim($code));
        $selected = $this->isSupportedCurrency($normalized) ? $normalized : $this->defaultCurrencyCode();

        session(['market_currency' => $selected]);
    }

    /**
     * Convert amount from source currency to selected or target currency.
     */
    public function convert(float $amount, string $fromCurrency, ?string $targetCurrency = null): float
    {
        $from = strtoupper($fromCurrency ?: 'USD');
        $to = strtoupper($targetCurrency ?: $this->selectedCurrencyCode());

        if ($from === $to) {
            return $amount;
        }

        $fromRate = $this->rateFromUsd($from);
        $toRate = $this->rateFromUsd($to);

        if ($fromRate <= 0 || $toRate <= 0) {
            return $amount;
        }

        $usd = $amount / $fromRate;

        return $usd * $toRate;
    }

    /**
     * Convert amount into USD.
     */
    public function convertToUsd(float $amount, string $fromCurrency): float
    {
        return $this->convert($amount, $fromCurrency, 'USD');
    }

    /**
     * Format an amount with currency symbol.
     */
    public function format(float $amount, string $currencyCode): string
    {
        $code = strtoupper($currencyCode ?: 'USD');

        return $this->symbol($code).number_format($amount, 2).' '.$code;
    }

    /**
     * Resolve symbol for a currency code.
     */
    public function symbol(string $currencyCode): string
    {
        $code = strtoupper($currencyCode ?: 'USD');

        if ($this->canUseCurrenciesTable()) {
            $symbol = Currency::query()->where('code', $code)->value('symbol');

            if ($symbol) {
                return $symbol;
            }
        }

        return $this->fallback[$code]['symbol'] ?? $code.' ';
    }

    /**
     * Determine default display currency.
     */
    public function defaultCurrencyCode(): string
    {
        if ($this->canUseCurrenciesTable()) {
            $default = Currency::query()->where('is_default', true)->value('code');

            if (is_string($default) && $default !== '') {
                return strtoupper($default);
            }
        }

        return 'USD';
    }

    /**
     * Check if a currency code is available.
     */
    public function isSupportedCurrency(string $code): bool
    {
        if ($this->canUseCurrenciesTable()) {
            return Currency::query()->where('code', strtoupper($code))->where('is_active', true)->exists();
        }

        return array_key_exists(strtoupper($code), $this->fallback);
    }

    /**
     * Get rate_from_usd for a currency.
     */
    public function rateFromUsd(string $code): float
    {
        $normalized = strtoupper($code ?: 'USD');

        if ($this->canUseCurrenciesTable()) {
            $rate = Currency::query()->where('code', $normalized)->value('rate_from_usd');

            if ($rate !== null) {
                return (float) $rate;
            }
        }

        return (float) ($this->fallback[$normalized]['rate_from_usd'] ?? 1.0);
    }

    /**
     * Guard access to currencies table when not yet migrated.
     */
    protected function canUseCurrenciesTable(): bool
    {
        try {
            return Schema::hasTable('currencies');
        } catch (QueryException) {
            return false;
        }
    }
}
