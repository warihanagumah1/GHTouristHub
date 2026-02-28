<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Tenant;

class PayoutService
{
    public function __construct(protected CurrencyService $currencyService)
    {
    }

    public function commissionPercent(): float
    {
        return max(0, min(100, (float) config('services.stripe.platform_commission_percent', 10)));
    }

    /**
     * @return array{commission: float, vendor_net: float}
     */
    public function splitAmount(float $grossAmount): array
    {
        $commission = round($grossAmount * ($this->commissionPercent() / 100), 2);
        $vendorNet = round(max(0, $grossAmount - $commission), 2);

        return [
            'commission' => $commission,
            'vendor_net' => $vendorNet,
        ];
    }

    /**
     * @return array{commission: int, vendor_net: int}
     */
    public function splitCents(int $grossCents): array
    {
        $commission = (int) round($grossCents * ($this->commissionPercent() / 100));
        $vendorNet = max(0, $grossCents - $commission);

        return [
            'commission' => $commission,
            'vendor_net' => $vendorNet,
        ];
    }

    public function tenantAvailableBalanceUsd(Tenant $tenant): float
    {
        $earnedUsd = Payment::query()
            ->where('status', 'paid')
            ->where('transfer_mode', 'platform')
            ->whereHas('booking', fn ($query) => $query->where('tenant_id', $tenant->id))
            ->get(['vendor_net_amount', 'currency'])
            ->sum(fn (Payment $payment) => $this->currencyService->convertToUsd(
                (float) $payment->vendor_net_amount,
                (string) $payment->currency
            ));

        $reservedOrPaidUsd = $tenant->payoutRequests()
            ->whereIn('status', ['pending', 'approved', 'paid'])
            ->get(['amount', 'currency'])
            ->sum(fn ($request) => $this->currencyService->convertToUsd((float) $request->amount, (string) $request->currency));

        return round(max(0, $earnedUsd - $reservedOrPaidUsd), 2);
    }
}
