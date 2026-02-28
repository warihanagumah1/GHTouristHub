<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Notifications\PayoutRequestSubmittedNotification;
use App\Services\PayoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PayoutRequestController extends Controller
{
    public function index(PayoutService $payoutService): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant, 403);

        $tenant->load('profile');

        $requests = $tenant->payoutRequests()
            ->with(['requester', 'processor'])
            ->latest()
            ->paginate(15);

        return view('vendor.payouts.index', [
            'tenant' => $tenant,
            'requests' => $requests,
            'availableBalanceUsd' => $payoutService->tenantAvailableBalanceUsd($tenant),
            'commissionPercent' => $payoutService->commissionPercent(),
        ]);
    }

    public function store(Request $request, PayoutService $payoutService): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant, 403);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $availableUsd = $payoutService->tenantAvailableBalanceUsd($tenant);
        $requestedUsd = (float) $validated['amount'];

        if ($requestedUsd > $availableUsd) {
            return back()->withErrors([
                'amount' => 'Requested amount exceeds available balance.',
            ]);
        }

        $payoutRequest = PayoutRequest::create([
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $request->user()->id,
            'amount' => $requestedUsd,
            'currency' => 'USD',
            'status' => 'pending',
            'note' => $validated['note'] ?? null,
        ]);

        $request->user()->notify(new PayoutRequestSubmittedNotification($payoutRequest));

        User::query()
            ->whereIn('user_role', [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF])
            ->each(fn (User $admin) => $admin->notify(new PayoutRequestSubmittedNotification($payoutRequest)));

        return back()->with('status', 'Payout request submitted.');
    }

    /**
     * Update vendor payout routing setup.
     */
    public function updateSetup(Request $request): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant, 403);

        $validated = $request->validate([
            'payout_mode' => ['required', 'in:platform_payouts,connect_destination'],
            'stripe_connect_account_id' => ['nullable', 'string', 'max:255', 'regex:/^acct_[A-Za-z0-9]+$/'],
        ], [
            'stripe_connect_account_id.regex' => 'Stripe Connect Account ID must start with acct_ and contain only letters/numbers.',
        ]);

        $payoutMode = (string) $validated['payout_mode'];
        $connectAccountId = $validated['stripe_connect_account_id'] ?? null;

        if ($payoutMode === 'connect_destination' && blank($connectAccountId)) {
            return back()->withErrors([
                'stripe_connect_account_id' => 'Stripe Connect Account ID is required for Stripe Connect payout mode.',
            ]);
        }

        $tenant->profile()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'payout_mode' => $payoutMode,
                'stripe_connect_account_id' => $payoutMode === 'connect_destination' ? $connectAccountId : null,
            ]
        );

        return back()->with('status', 'Payout setup updated.');
    }
}
