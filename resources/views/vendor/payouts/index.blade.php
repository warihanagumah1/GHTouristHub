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

            <div class="grid gap-4 md:grid-cols-4">
                <x-stat-card label="Available Balance (USD)" :value="'$'.number_format((float) $availableBalanceUsd, 2)" />
                <x-stat-card label="Outstanding Requests (USD)" :value="'$'.number_format((float) $outstandingBalanceUsd, 2)" />
                <x-stat-card label="Commission Rate" :value="$commissionPercent.'%'" />
                <x-stat-card label="Payout Mode" :value="$tenant->profile?->payout_mode === 'connect_destination' ? 'Stripe Connect' : 'Platform Payouts'" />
            </div>

            <x-card title="Payout Setup">
                @php
                    $selectedManualType = old('manual_payout_type', data_get($manualPayoutSetup, 'type', ''));
                    $mobileNetworks = ['MTN', 'Telecel', 'AirtelTigo', 'Glo', 'Other'];
                    $selectedMobileProvider = old('manual_mobile_provider', data_get($manualPayoutSetup, 'mobile_provider'));
                @endphp
                <form method="POST" action="{{ route('vendor.payouts.setup') }}" class="grid gap-4 md:grid-cols-2" x-data="{ manualType: @js($selectedManualType) }">
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
                    <div class="md:col-span-2 rounded-xl border border-slate-200 p-4">
                        <h3 class="text-sm font-semibold text-primary">Preferred Manual Payout Details</h3>
                        <p class="mt-1 text-xs text-primary/60">These details are shown to admin when processing manual payout requests.</p>

                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <x-input-label for="manual_payout_type" value="Payout Detail Type" />
                                <x-select-input id="manual_payout_type" name="manual_payout_type" class="mt-1" x-model="manualType">
                                    <option value="">Select type</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="other">Other</option>
                                </x-select-input>
                                <x-input-error :messages="$errors->get('manual_payout_type')" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="manual_account_name" value="Account Name" />
                                <x-text-input
                                    id="manual_account_name"
                                    name="manual_account_name"
                                    class="mt-1"
                                    maxlength="255"
                                    :value="old('manual_account_name', data_get($manualPayoutSetup, 'account_name'))"
                                />
                                <x-input-error :messages="$errors->get('manual_account_name')" class="mt-1" />
                            </div>

                            <div class="contents" x-cloak x-show="manualType === 'bank_transfer'">
                                <div>
                                    <x-input-label for="manual_bank_name" value="Bank Name" />
                                    <x-text-input id="manual_bank_name" name="manual_bank_name" class="mt-1" maxlength="255" :value="old('manual_bank_name', data_get($manualPayoutSetup, 'bank_name'))" />
                                    <x-input-error :messages="$errors->get('manual_bank_name')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="manual_bank_branch" value="Branch (optional)" />
                                    <x-text-input id="manual_bank_branch" name="manual_bank_branch" class="mt-1" maxlength="255" :value="old('manual_bank_branch', data_get($manualPayoutSetup, 'bank_branch'))" />
                                    <x-input-error :messages="$errors->get('manual_bank_branch')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="manual_bank_account_number" value="Account Number" />
                                    <x-text-input id="manual_bank_account_number" name="manual_bank_account_number" class="mt-1" maxlength="120" :value="old('manual_bank_account_number', data_get($manualPayoutSetup, 'bank_account_number'))" />
                                    <x-input-error :messages="$errors->get('manual_bank_account_number')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="manual_bank_swift_code" value="SWIFT Code (optional)" />
                                    <x-text-input id="manual_bank_swift_code" name="manual_bank_swift_code" class="mt-1" maxlength="120" :value="old('manual_bank_swift_code', data_get($manualPayoutSetup, 'bank_swift_code'))" />
                                    <x-input-error :messages="$errors->get('manual_bank_swift_code')" class="mt-1" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="manual_bank_iban" value="IBAN (optional)" />
                                    <x-text-input id="manual_bank_iban" name="manual_bank_iban" class="mt-1" maxlength="120" :value="old('manual_bank_iban', data_get($manualPayoutSetup, 'bank_iban'))" />
                                    <x-input-error :messages="$errors->get('manual_bank_iban')" class="mt-1" />
                                </div>
                            </div>

                            <div class="contents" x-cloak x-show="manualType === 'mobile_money'">
                                <div>
                                    <x-input-label for="manual_mobile_provider" value="Mobile Network" />
                                    <x-select-input id="manual_mobile_provider" name="manual_mobile_provider" class="mt-1">
                                        <option value="">Select mobile network</option>
                                        @foreach ($mobileNetworks as $network)
                                            <option value="{{ $network }}" @selected($selectedMobileProvider === $network)>{{ $network }}</option>
                                        @endforeach
                                        @if (filled($selectedMobileProvider) && ! in_array($selectedMobileProvider, $mobileNetworks, true))
                                            <option value="{{ $selectedMobileProvider }}" selected>{{ $selectedMobileProvider }} (Saved)</option>
                                        @endif
                                    </x-select-input>
                                    <x-input-error :messages="$errors->get('manual_mobile_provider')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="manual_mobile_number" value="Mobile Money Number" />
                                    <x-text-input id="manual_mobile_number" name="manual_mobile_number" class="mt-1" maxlength="120" :value="old('manual_mobile_number', data_get($manualPayoutSetup, 'mobile_number'))" placeholder="+233..." />
                                    <x-input-error :messages="$errors->get('manual_mobile_number')" class="mt-1" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="manual_mobile_account_name" value="Mobile Money Account Name" />
                                    <x-text-input id="manual_mobile_account_name" name="manual_mobile_account_name" class="mt-1" maxlength="255" :value="old('manual_mobile_account_name', data_get($manualPayoutSetup, 'mobile_account_name'))" />
                                    <x-input-error :messages="$errors->get('manual_mobile_account_name')" class="mt-1" />
                                </div>
                            </div>

                            <div class="md:col-span-2" x-cloak x-show="manualType === 'paypal'">
                                <x-input-label for="manual_paypal_email" value="PayPal Email" />
                                <x-text-input id="manual_paypal_email" name="manual_paypal_email" type="email" class="mt-1" maxlength="255" :value="old('manual_paypal_email', data_get($manualPayoutSetup, 'paypal_email'))" />
                                <x-input-error :messages="$errors->get('manual_paypal_email')" class="mt-1" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="manual_notes" value="Additional Notes" />
                                <x-textarea-input id="manual_notes" name="manual_notes" class="mt-1" rows="3" maxlength="2000" placeholder="Any extra payout instructions">{{ old('manual_notes', data_get($manualPayoutSetup, 'notes')) }}</x-textarea-input>
                                <x-input-error :messages="$errors->get('manual_notes')" class="mt-1" />
                            </div>
                        </div>

                        <input type="hidden" name="preferred_payout_details" value="{{ old('preferred_payout_details') }}">
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
