<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Payout Requests</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-3">
                    <x-select-input name="status">
                        <option value="">All statuses</option>
                        @foreach (['pending', 'approved', 'paid', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-select-input>
                    <x-select-input name="tenant_id">
                        <option value="">All vendors</option>
                        @foreach ($tenants as $tenant)
                            <option value="{{ $tenant->id }}" @selected((string) request('tenant_id') === (string) $tenant->id)>{{ $tenant->name }}</option>
                        @endforeach
                    </x-select-input>
                    <x-button type="submit" variant="secondary">Apply</x-button>
                </form>
            </x-card>

            <x-card title="Payout Requests">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Vendor</th>
                                <th class="py-2 pe-4">Requester</th>
                                <th class="py-2 pe-4">Amount</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Preferred Payout Details</th>
                                <th class="py-2 pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $request)
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="py-3 pe-4 text-primary">{{ $request->tenant->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $request->requester->email }}</td>
                                    <td class="py-3 pe-4 text-primary">${{ number_format((float) $request->amount, 2) }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/80">{{ $request->status }}</td>
                                    <td class="py-3 pe-4 text-primary/75">
                                        @php
                                            $storedDetails = $request->tenant->profile?->preferred_payout_details;
                                            $decodedDetails = is_string($storedDetails) ? json_decode($storedDetails, true) : null;
                                            $typeLabels = [
                                                'bank_transfer' => 'Bank Transfer',
                                                'mobile_money' => 'Mobile Money',
                                                'paypal' => 'PayPal',
                                                'other' => 'Other',
                                            ];
                                            $detailFields = [
                                                'account_name' => 'Account Name',
                                                'bank_name' => 'Bank Name',
                                                'bank_branch' => 'Branch',
                                                'bank_account_number' => 'Account Number',
                                                'bank_swift_code' => 'SWIFT',
                                                'bank_iban' => 'IBAN',
                                                'mobile_provider' => 'Network',
                                                'mobile_number' => 'Mobile Number',
                                                'mobile_account_name' => 'Mobile Account Name',
                                                'paypal_email' => 'PayPal Email',
                                                'notes' => 'Notes',
                                            ];
                                        @endphp
                                        @if (is_array($decodedDetails) && filled($decodedDetails['type'] ?? null))
                                            <div class="space-y-1">
                                                <p><span class="font-semibold text-primary/90">Type:</span> {{ $typeLabels[$decodedDetails['type']] ?? ucfirst(str_replace('_', ' ', $decodedDetails['type'])) }}</p>
                                                @foreach ($detailFields as $fieldKey => $fieldLabel)
                                                    @if (filled($decodedDetails[$fieldKey] ?? null))
                                                        <p><span class="font-semibold text-primary/90">{{ $fieldLabel }}:</span> {{ $decodedDetails[$fieldKey] }}</p>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif (filled($storedDetails))
                                            {{ $storedDetails }}
                                        @else
                                            Not provided
                                        @endif
                                    </td>
                                    <td class="py-3 pe-4">
                                        @if (in_array($request->status, ['pending', 'approved'], true))
                                            <form method="POST" action="{{ route('admin.payouts.update', $request) }}" class="space-y-2">
                                                @csrf
                                                @method('PUT')
                                                <div class="flex gap-2">
                                                    <x-select-input name="status" class="w-36 text-xs">
                                                        @if ($request->status === 'pending')
                                                            <option value="approved">Approve</option>
                                                            <option value="rejected">Reject</option>
                                                        @else
                                                            <option value="paid">Mark Paid</option>
                                                            <option value="rejected">Reject</option>
                                                        @endif
                                                    </x-select-input>
                                                    <x-text-input name="stripe_transfer_id" placeholder="Transfer ref" class="w-44 text-xs" />
                                                    <x-button type="submit" variant="outline" class="text-[10px]">Update</x-button>
                                                </div>
                                                <p class="text-[11px] text-primary/60">
                                                    Leave transfer ref empty to auto-transfer via Stripe when connected account + Stripe config are available.
                                                </p>
                                                <x-text-input name="admin_note" placeholder="Admin note" class="w-full text-xs" />
                                            </form>
                                        @else
                                            <span class="text-xs text-primary/60">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-primary/70">No payout requests found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-6">{{ $requests->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>
