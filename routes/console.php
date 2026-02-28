<?php

use App\Models\Currency;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('currencies:sync-rates', function () {
    $apiKey = (string) config('services.exchangerate.key');
    $baseUrl = rtrim((string) config('services.exchangerate.url', ''), '/');

    if ($apiKey === '' || $baseUrl === '') {
        $this->error('ExchangeRate API configuration is missing.');

        return 1;
    }

    $response = Http::timeout(20)->get("{$baseUrl}/{$apiKey}/latest/USD");

    if (! $response->successful()) {
        $this->error('Failed to fetch exchange rates.');

        return 1;
    }

    $rates = (array) $response->json('conversion_rates', []);
    if ($rates === []) {
        $this->error('No conversion rates returned by the API.');

        return 1;
    }

    $updated = 0;
    foreach (Currency::query()->where('is_active', true)->get() as $currency) {
        $rate = $rates[$currency->code] ?? null;

        if ($rate === null || (float) $rate <= 0) {
            continue;
        }

        $currency->update([
            'rate_from_usd' => (float) $rate,
            'last_synced_at' => now(),
        ]);

        $updated++;
    }

    $this->info("Updated {$updated} active currencies from ExchangeRate API.");

    return 0;
})->purpose('Sync currency rates from ExchangeRate API');

Schedule::command('currencies:sync-rates')->twiceDaily(0, 12);
