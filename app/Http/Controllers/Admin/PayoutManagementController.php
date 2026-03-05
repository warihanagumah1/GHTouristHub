<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Models\Tenant;
use App\Notifications\PayoutRequestPaidNotification;
use App\Services\StripeTransferService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PayoutManagementController extends Controller
{
    public function __construct(protected StripeTransferService $stripeTransferService)
    {
    }

    public function index(Request $request): View
    {
        $query = PayoutRequest::query()
            ->with(['tenant.profile', 'requester', 'processor']);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', (int) $request->query('tenant_id'));
        }

        $requests = $query->latest()->paginate(25)->withQueryString();

        return view('admin.payouts.index', [
            'requests' => $requests,
            'tenants' => Tenant::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, PayoutRequest $payoutRequest): RedirectResponse
    {
        $payoutRequest->loadMissing('tenant.profile');

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'paid', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
            'stripe_transfer_id' => ['nullable', 'string', 'max:255'],
        ]);

        $nextStatus = (string) $validated['status'];
        $currentStatus = (string) $payoutRequest->status;

        $allowedTransitions = [
            'pending' => ['approved', 'rejected'],
            'approved' => ['paid', 'rejected'],
            'paid' => [],
            'rejected' => [],
        ];

        if (! in_array($nextStatus, $allowedTransitions[$currentStatus] ?? [], true)) {
            return back()->withErrors([
                'status' => "Invalid status transition from {$currentStatus} to {$nextStatus}.",
            ]);
        }

        $stripeTransferId = $validated['stripe_transfer_id'] ?? $payoutRequest->stripe_transfer_id;

        if ($nextStatus === 'paid' && blank($stripeTransferId)) {
            $connectEnabled = (bool) config('services.stripe.connect_destination_enabled', false);
            $destinationAccount = (string) ($payoutRequest->tenant->profile?->stripe_connect_account_id ?? '');

            if ($connectEnabled && $destinationAccount !== '' && (string) config('services.stripe.secret') !== '') {
                try {
                    $stripeTransferId = $this->stripeTransferService->createTransfer(
                        amountCents: (int) round((float) $payoutRequest->amount * 100),
                        currencyCode: (string) $payoutRequest->currency,
                        destinationAccountId: $destinationAccount,
                        metadata: [
                            'payout_request_id' => $payoutRequest->id,
                            'tenant_id' => $payoutRequest->tenant_id,
                        ],
                    );
                } catch (RuntimeException $exception) {
                    return back()->withErrors([
                        'stripe_transfer_id' => 'Unable to create Stripe transfer automatically. Add a manual transfer reference or retry after checking Stripe settings.',
                    ]);
                }
            }
        }

        $payoutRequest->update([
            'status' => $nextStatus,
            'admin_note' => $validated['admin_note'] ?? $payoutRequest->admin_note,
            'stripe_transfer_id' => $stripeTransferId,
            'processed_by_user_id' => $request->user()->id,
            'processed_at' => now(),
        ]);

        if ($nextStatus === 'paid') {
            $payoutRequest->requester?->notify(new PayoutRequestPaidNotification($payoutRequest->fresh()));
        }

        return back()->with('status', 'Payout request updated.');
    }
}
