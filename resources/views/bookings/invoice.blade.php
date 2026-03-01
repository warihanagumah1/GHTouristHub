<x-app-layout>
    @php
        $latestPayment = $booking->payments->sortByDesc('created_at')->first();
        $invoiceNumber = 'INV-'.strtoupper(str_replace('THB-', '', (string) $booking->booking_no));
        $isVendorAudience = $audience === 'vendor';
        $backRoute = $isVendorAudience ? route('vendor.bookings.show', $booking) : route('client.bookings.show', $booking);
    @endphp

    <style>
        @media print {
            @page {
                margin: 12mm;
                size: A4 portrait;
            }

            nav,
            header,
            footer,
            .print-hide {
                display: none !important;
            }

            main {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            #invoice-print-root {
                width: 100%;
                max-width: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #invoice-print-root .fc-card {
                border: 0 !important;
                box-shadow: none !important;
                padding: 0 !important;
            }

            .print-break-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Invoice {{ $invoiceNumber }}</h2>
    </x-slot>

    <div class="py-12">
        <div id="invoice-print-root" class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success" class="print-hide">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger" class="print-hide">{{ $errors->first() }}</x-alert>
            @endif

            <div class="print-hide flex flex-wrap items-center justify-between gap-3">
                <a href="{{ $backRoute }}" class="fc-link">← Back to booking {{ $booking->booking_no }}</a>
                <div class="flex flex-wrap items-center gap-2">
                    @if ($isVendorAudience)
                        <form method="POST" action="{{ route('vendor.bookings.invoice.email', $booking) }}">
                            @csrf
                            <x-button type="submit" variant="secondary">Email Invoice to Client</x-button>
                        </form>
                    @endif
                    <button type="button" onclick="window.print()" class="fc-btn fc-btn-outline">Print invoice</button>
                </div>
            </div>

            <x-card class="print-break-avoid">
                <div class="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <img src="{{ asset('images/logo/logo.png') }}" alt="GH Tourist Hub logo" class="h-12 w-auto" />
                        <p class="text-xs uppercase tracking-widest text-primary/60">Billed by</p>
                        <h3 class="mt-1 text-2xl font-bold text-primary">GH Tourist Hub</h3>
                        <p class="mt-2 text-sm text-primary/75">support@ghtouristhub.com</p>
                        <p class="text-sm text-primary/75">+233 20 000 0000</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs uppercase tracking-widest text-primary/60">Invoice details</p>
                        <p class="mt-1 text-sm text-primary/80"><span class="font-semibold">Invoice:</span> {{ $invoiceNumber }}</p>
                        <p class="text-sm text-primary/80"><span class="font-semibold">Booking:</span> {{ $booking->booking_no }}</p>
                        <p class="text-sm text-primary/80"><span class="font-semibold">Date:</span> {{ $booking->created_at->format('M d, Y') }}</p>
                        <p class="text-sm text-primary/80"><span class="font-semibold">Status:</span> {{ ucfirst(str_replace('_', ' ', $booking->status)) }}</p>
                    </div>
                </div>

                <div class="mt-8 grid gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 md:grid-cols-2">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-primary/60">Bill to</p>
                        <p class="mt-1 text-sm font-semibold text-primary">{{ $booking->user->name }}</p>
                        <p class="text-sm text-primary/75">{{ $booking->user->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-widest text-primary/60">Vendor</p>
                        <p class="mt-1 text-sm font-semibold text-primary">{{ $booking->tenant->name }}</p>
                        <p class="text-sm text-primary/75">{{ $booking->listing->city }}, {{ $booking->listing->country }}</p>
                    </div>
                </div>

                <div class="mt-8 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Item</th>
                                <th class="py-2 pe-4">Quantity</th>
                                <th class="py-2 pe-4">Unit Price</th>
                                <th class="py-2 pe-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b border-slate-100">
                                <td class="py-3 pe-4 text-primary">
                                    <p class="font-semibold">{{ $booking->listing->title }}</p>
                                    <p class="text-xs text-primary/65 capitalize">{{ $booking->listing->type }} service</p>
                                </td>
                                <td class="py-3 pe-4 text-primary/80">{{ $booking->travelers_count }}</td>
                                <td class="py-3 pe-4 text-primary/80">
                                    <x-money :amount="(float) $booking->total_amount / max(1, (int) $booking->travelers_count)" :from="$booking->currency" show-original />
                                </td>
                                <td class="py-3 pe-4 text-right text-primary/90">
                                    <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="pt-4 text-right text-sm font-semibold text-primary">Total</td>
                                <td class="pt-4 text-right text-xl font-bold text-primary">
                                    <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="mt-6 rounded-lg border border-tertiary/40 bg-tertiary/10 p-4 text-sm text-primary/80 print-break-avoid">
                    <p>
                        Payment method:
                        <span class="font-semibold capitalize">{{ $latestPayment?->provider ?? 'stripe' }}</span>
                        • {{ strtoupper((string) ($latestPayment?->status ?? 'pending')) }}
                    </p>
                    <p class="mt-1">
                        @if ($isVendorAudience)
                            Use this invoice for fulfillment and accounting records.
                        @else
                            Keep this invoice for your booking and travel records.
                        @endif
                    </p>
                </div>
            </x-card>
        </div>
    </div>

    <script>
        (() => {
            const invoiceTitle = @json($invoiceNumber);
            if (invoiceTitle && document.title !== invoiceTitle) {
                document.title = invoiceTitle;
            }
        })();
    </script>
</x-app-layout>
