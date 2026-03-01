<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                <x-stat-card label="Upcoming / Paid" :value="$stats['upcoming']" />
                <x-stat-card label="Pending Payment" :value="$stats['pending_payment']" />
                <x-stat-card label="Completed Trips" :value="$stats['completed']" />
            </div>

            <x-card title="Total Spent">
                <p class="text-3xl font-bold text-primary">${{ number_format($stats['total_spent_usd'], 2) }}</p>
                <p class="mt-1 text-sm text-primary/70">Across all paid bookings on {{ config('app.name', 'GH Tourist Hub') }} (USD equivalent).</p>
            </x-card>

            <x-card title="Recent Bookings">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Booking</th>
                                <th class="py-2 pe-4">Listing</th>
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
                                    <td colspan="5" class="py-6 text-center text-primary/70">No bookings yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <a href="{{ route('client.bookings.index') }}" class="fc-btn fc-btn-secondary">View All Bookings</a>
                </div>
            </x-card>

            <div class="grid gap-4 md:grid-cols-2">
                <x-card title="Discover more tours">
                    <p class="text-sm text-primary/75">Explore curated tour packages and new utility listings in top destinations.</p>
                    <a href="{{ route('marketplace.tours') }}" class="fc-btn fc-btn-outline mt-4">Browse Tours</a>
                </x-card>
                <x-card title="Messages and invoices">
                    <p class="text-sm text-primary/75">Open any booking to chat with the company and view your invoice.</p>
                    <a href="{{ route('client.bookings.index') }}" class="fc-btn fc-btn-outline mt-4">Open Bookings</a>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
