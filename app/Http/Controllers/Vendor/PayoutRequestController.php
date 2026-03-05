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
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

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
            'outstandingBalanceUsd' => $payoutService->tenantOutstandingBalanceUsd($tenant),
            'commissionPercent' => $payoutService->commissionPercent(),
            'manualPayoutSetup' => $this->preferredPayoutSetup($tenant->profile?->preferred_payout_details),
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
            'manual_payout_type' => ['nullable', Rule::in(['bank_transfer', 'mobile_money', 'paypal', 'other'])],
            'manual_account_name' => ['nullable', 'string', 'max:255'],
            'manual_bank_name' => ['nullable', 'string', 'max:255'],
            'manual_bank_branch' => ['nullable', 'string', 'max:255'],
            'manual_bank_account_number' => ['nullable', 'string', 'max:120'],
            'manual_bank_swift_code' => ['nullable', 'string', 'max:120'],
            'manual_bank_iban' => ['nullable', 'string', 'max:120'],
            'manual_mobile_provider' => ['nullable', 'string', 'max:120'],
            'manual_mobile_number' => ['nullable', 'string', 'max:120'],
            'manual_mobile_account_name' => ['nullable', 'string', 'max:255'],
            'manual_paypal_email' => ['nullable', 'email', 'max:255'],
            'manual_notes' => ['nullable', 'string', 'max:2000'],
            'preferred_payout_details' => ['nullable', 'string', 'max:5000'],
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

        $manualType = (string) ($validated['manual_payout_type'] ?? '');
        $manualFields = [
            'manual_account_name',
            'manual_bank_name',
            'manual_bank_branch',
            'manual_bank_account_number',
            'manual_bank_swift_code',
            'manual_bank_iban',
            'manual_mobile_provider',
            'manual_mobile_number',
            'manual_mobile_account_name',
            'manual_paypal_email',
            'manual_notes',
        ];
        $hasManualDetails = collect($manualFields)->contains(fn (string $field): bool => filled($validated[$field] ?? null));

        if (($manualType !== '' || $hasManualDetails) && $manualType === '') {
            return back()->withErrors([
                'manual_payout_type' => 'Select a payout detail type before filling manual payout details.',
            ])->withInput();
        }

        if ($manualType === 'bank_transfer') {
            $requiredBankFields = ['manual_account_name', 'manual_bank_name', 'manual_bank_account_number'];
            foreach ($requiredBankFields as $field) {
                if (blank($validated[$field] ?? null)) {
                    return back()->withErrors([
                        $field => 'Bank transfer details require account name, bank name, and account number.',
                    ])->withInput();
                }
            }
        }

        if ($manualType === 'mobile_money') {
            foreach (['manual_mobile_provider', 'manual_mobile_number', 'manual_mobile_account_name'] as $field) {
                if (blank($validated[$field] ?? null)) {
                    return back()->withErrors([
                        $field => 'Mobile money details require provider, number, and account name.',
                    ])->withInput();
                }
            }
        }

        if ($manualType === 'paypal' && blank($validated['manual_paypal_email'] ?? null)) {
            return back()->withErrors([
                'manual_paypal_email' => 'PayPal details require a PayPal email.',
            ])->withInput();
        }

        $serializedPayoutDetails = $this->serializePreferredPayoutDetails($validated);
        if ($serializedPayoutDetails === null) {
            $legacyDetails = trim((string) ($validated['preferred_payout_details'] ?? ''));
            $serializedPayoutDetails = $legacyDetails !== '' ? $legacyDetails : null;
        }

        $tenant->profile()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'payout_mode' => $payoutMode,
                'stripe_connect_account_id' => $payoutMode === 'connect_destination' ? $connectAccountId : null,
                'preferred_payout_details' => $serializedPayoutDetails,
            ]
        );

        return back()->with('status', 'Payout setup updated.');
    }

    /**
     * Parse stored payout details into structured fields for the setup form.
     *
     * @return array<string, string>
     */
    protected function preferredPayoutSetup(?string $storedValue): array
    {
        $defaults = [
            'type' => '',
            'account_name' => '',
            'bank_name' => '',
            'bank_branch' => '',
            'bank_account_number' => '',
            'bank_swift_code' => '',
            'bank_iban' => '',
            'mobile_provider' => '',
            'mobile_number' => '',
            'mobile_account_name' => '',
            'paypal_email' => '',
            'notes' => '',
        ];

        if (blank($storedValue)) {
            return $defaults;
        }

        $decoded = json_decode($storedValue, true);
        if (is_array($decoded)) {
            $normalized = Arr::only($decoded, array_keys($defaults));

            return array_merge($defaults, array_map(
                fn ($value) => is_scalar($value) ? (string) $value : '',
                $normalized
            ));
        }

        return array_merge($defaults, [
            'type' => 'other',
            'notes' => $storedValue,
        ]);
    }

    /**
     * Serialize structured manual payout fields into JSON for storage.
     */
    protected function serializePreferredPayoutDetails(array $validated): ?string
    {
        $type = (string) ($validated['manual_payout_type'] ?? '');
        if ($type === '') {
            return null;
        }

        $details = [
            'type' => $type,
            'account_name' => (string) ($validated['manual_account_name'] ?? ''),
            'bank_name' => (string) ($validated['manual_bank_name'] ?? ''),
            'bank_branch' => (string) ($validated['manual_bank_branch'] ?? ''),
            'bank_account_number' => (string) ($validated['manual_bank_account_number'] ?? ''),
            'bank_swift_code' => (string) ($validated['manual_bank_swift_code'] ?? ''),
            'bank_iban' => (string) ($validated['manual_bank_iban'] ?? ''),
            'mobile_provider' => (string) ($validated['manual_mobile_provider'] ?? ''),
            'mobile_number' => (string) ($validated['manual_mobile_number'] ?? ''),
            'mobile_account_name' => (string) ($validated['manual_mobile_account_name'] ?? ''),
            'paypal_email' => (string) ($validated['manual_paypal_email'] ?? ''),
            'notes' => (string) ($validated['manual_notes'] ?? ''),
        ];

        $details = array_filter(
            $details,
            fn ($value, $key): bool => $key === 'type' || filled($value),
            ARRAY_FILTER_USE_BOTH
        );

        return json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: null;
    }
}
