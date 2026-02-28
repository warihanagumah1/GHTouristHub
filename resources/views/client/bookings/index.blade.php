<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">My Bookings</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-card>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Booking No</th>
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Vendor</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Total</th>
                                <th class="py-2 pe-4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $booking->booking_no }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $booking->listing->title }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $booking->listing->tenant->name }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $booking->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                                    </td>
                                    <td class="py-3 pe-4">
                                        <a href="{{ route('client.bookings.show', $booking) }}" class="fc-link">Open</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-primary/70">No bookings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $bookings->links() }}
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
