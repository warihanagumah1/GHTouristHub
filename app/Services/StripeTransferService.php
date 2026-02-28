<?php

namespace App\Services;

use Illuminate\Http\Client\Factory as HttpFactory;
use RuntimeException;

class StripeTransferService
{
    public function __construct(protected HttpFactory $http)
    {
    }

    /**
     * Create a transfer from the platform Stripe balance to a connected account.
     */
    public function createTransfer(
        int $amountCents,
        string $currencyCode,
        string $destinationAccountId,
        array $metadata = []
    ): string {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe secret is not configured.');
        }

        if ($amountCents < 1) {
            throw new RuntimeException('Transfer amount must be at least 1 cent.');
        }

        if ($destinationAccountId === '') {
            throw new RuntimeException('Missing connected Stripe account for transfer destination.');
        }

        $payload = [
            'amount' => $amountCents,
            'currency' => strtolower($currencyCode),
            'destination' => $destinationAccountId,
        ];

        foreach ($metadata as $key => $value) {
            if ($value !== null && $value !== '') {
                $payload["metadata[{$key}]"] = (string) $value;
            }
        }

        $response = $this->http
            ->withBasicAuth($secret, '')
            ->asForm()
            ->post('https://api.stripe.com/v1/transfers', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Stripe transfer request failed.');
        }

        $transferId = (string) $response->json('id');

        if ($transferId === '') {
            throw new RuntimeException('Stripe transfer response did not include a transfer id.');
        }

        return $transferId;
    }
}
