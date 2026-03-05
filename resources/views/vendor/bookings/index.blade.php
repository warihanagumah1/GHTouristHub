<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Vendor Bookings</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success" class="mb-4">{{ session('status') }}</x-alert>
            @endif
            @if ($errors->any())
                <x-alert variant="danger" class="mb-4">{{ $errors->first() }}</x-alert>
            @endif

            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Booking</th>
                                <th class="py-2 pe-4">Client</th>
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Amount</th>
                                <th class="py-2 pe-4">Payment</th>
                                <th class="py-2 pe-4">Update</th>
                                <th class="py-2 pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="border-b border-slate-100 align-top">
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $booking->booking_no }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $booking->user->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $booking->listing->title }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $booking->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                                    </td>
                                    <td class="py-3 pe-4 text-primary/75 capitalize">{{ $booking->payments->last()?->status ?? 'n/a' }}</td>
                                    <td class="py-3 pe-4">
                                        <form method="POST" action="{{ route('vendor.bookings.status', $booking) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PUT')
                                            <x-select-input name="status" class="w-40 text-xs">
                                                @php
                                                    $allowedStatuses = match ($booking->status) {
                                                        'pending_payment' => ['pending_payment', 'paid', 'cancelled'],
                                                        'paid' => ['paid', 'confirmed', 'completed', 'cancelled'],
                                                        'confirmed' => ['confirmed', 'completed', 'cancelled'],
                                                        'completed' => ['completed'],
                                                        default => ['cancelled'],
                                                    };
                                                @endphp
                                                @foreach ($allowedStatuses as $status)
                                                    <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                                @endforeach
                                            </x-select-input>
                                            <x-button type="submit" variant="outline" class="text-[10px]">Save</x-button>
                                        </form>
                                    </td>
                                    <td class="py-3 pe-4">
                                        <div class="flex flex-col gap-2">
                                            <a href="{{ route('vendor.bookings.show', $booking) }}" class="fc-link">Open</a>
                                            <a href="{{ route('vendor.bookings.invoice', $booking) }}" class="fc-link">Invoice</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-6 text-center text-primary/70">No bookings yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">{{ $bookings->links() }}</div>
            </x-card>
        </div>
    </div>
</x-app-layout>
