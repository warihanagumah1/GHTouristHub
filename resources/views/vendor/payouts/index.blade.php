<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Vendor Payouts</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <x-stat-card label="Available Balance (USD)" :value="'$'.number_format((float) $availableBalanceUsd, 2)" />
                <x-stat-card label="Commission Rate" :value="$commissionPercent.'%'" />
                <x-stat-card label="Payout Mode" :value="$tenant->profile?->payout_mode === 'connect_destination' ? 'Stripe Connect' : 'Platform Payouts'" />
            </div>

            <x-card title="Payout Setup">
                <form method="POST" action="{{ route('vendor.payouts.setup') }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="payout_mode" value="Payout Mode" />
                        <x-select-input id="payout_mode" name="payout_mode" class="mt-1">
                            <option value="platform_payouts" @selected(old('payout_mode', $tenant->profile?->payout_mode ?? 'platform_payouts') === 'platform_payouts')>Platform Payout Requests</option>
                            <option value="connect_destination" @selected(old('payout_mode', $tenant->profile?->payout_mode ?? 'platform_payouts') === 'connect_destination')>Stripe Connect Destination Charges</option>
                        </x-select-input>
                        <p class="mt-1 text-xs text-primary/60">Choose where your booking funds settle.</p>
                    </div>
                    <div>
                        <x-input-label for="stripe_connect_account_id" value="Stripe Connect Account ID" />
                        <x-text-input
                            id="stripe_connect_account_id"
                            name="stripe_connect_account_id"
                            class="mt-1"
                            placeholder="acct_123..."
                            maxlength="255"
                            :value="old('stripe_connect_account_id', $tenant->profile?->stripe_connect_account_id)"
                        />
                        <p class="mt-1 text-xs text-primary/60">Required for Stripe Connect mode. Format: <code>acct_...</code></p>
                    </div>
                    <div class="md:col-span-2">
                        <x-button type="submit" variant="secondary">Save Payout Setup</x-button>
                    </div>
                </form>
            </x-card>

            <x-card title="Payout Flow (Short)">
                <p class="text-sm text-primary/80">
                    1) Customer pays for a booking. 2) Your net balance is calculated after platform commission.
                    3) If you use platform payouts, submit a payout request here and admin processes it.
                    4) If you use Stripe Connect destination charges, funds route to your connected Stripe account.
                </p>
            </x-card>

            <x-card title="Request Payout">
                @if (($tenant->profile?->payout_mode ?? 'platform_payouts') === 'connect_destination')
                    <x-alert variant="info">
                        Your payout mode is set to Stripe Connect destination charges. Funds are routed directly to your connected account.
                    </x-alert>
                @else
                    <form method="POST" action="{{ route('vendor.payouts.store') }}" class="grid gap-3 md:grid-cols-3">
                        @csrf
                        <div>
                            <x-input-label for="amount" value="Amount (USD)" />
                            <x-text-input id="amount" name="amount" type="number" step="0.01" min="1" class="mt-1" :value="old('amount')" required />
                        </div>
                        <div class="md:col-span-2">
                            <x-input-label for="note" value="Note (optional)" />
                            <x-text-input id="note" name="note" class="mt-1" :value="old('note')" />
                        </div>
                        <div class="md:col-span-3">
                            <x-primary-button>Submit Payout Request</x-primary-button>
                        </div>
                    </form>
                @endif
            </x-card>

            <x-card title="Payout Requests">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Date</th>
                                <th class="py-2 pe-4">Amount</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Note</th>
                                <th class="py-2 pe-4">Admin Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 text-primary">{{ $request->created_at->format('Y-m-d') }}</td>
                                    <td class="py-3 pe-4 text-primary">${{ number_format((float) $request->amount, 2) }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ $request->status }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $request->note ?: '—' }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $request->admin_note ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 text-center text-primary/70">No payout requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $requests->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
