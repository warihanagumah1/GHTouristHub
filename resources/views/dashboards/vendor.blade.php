<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Vendor Dashboard') }} • {{ $tenant->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <div class="grid gap-4 md:grid-cols-5">
                <x-stat-card label="Active Listings" :value="$stats['active_listings']" />
                <x-stat-card label="Total Bookings" :value="$stats['bookings_total']" />
                <x-stat-card label="Pending Payments" :value="$stats['pending_bookings']" />
                <x-stat-card label="Paid Revenue (USD)" :value="'$'.number_format($stats['revenue_paid_usd'], 2)" />
                <x-stat-card
                    label="Ratings"
                    :value="number_format((float) $stats['average_rating'], 1).'/5'"
                    :trend="$stats['total_reviews'].' review'.($stats['total_reviews'] === 1 ? '' : 's')"
                />
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <a href="{{ route('vendor.listings.index') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Manage Listings</h3>
                    <p class="mt-1 text-sm text-primary/75">Create, edit, publish, and pause tour/utility packages.</p>
                </a>
                <a href="{{ route('vendor.bookings.index') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Manage Bookings</h3>
                    <p class="mt-1 text-sm text-primary/75">Confirm bookings, monitor payment status, and update completion.</p>
                </a>
                <a href="{{ route('vendor.analytics') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Analytics</h3>
                    <p class="mt-1 text-sm text-primary/75">Track booking trends, revenue performance, and top listings.</p>
                </a>
                <a href="{{ route('vendor.reviews.index') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Customer Reviews</h3>
                    <p class="mt-1 text-sm text-primary/75">See company ratings and read client feedback on your listings.</p>
                </a>
                <a href="{{ route('vendor.site-profile.edit') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Mini Website Profile</h3>
                    <p class="mt-1 text-sm text-primary/75">Edit company bio, logo, banner, and contact details.</p>
                </a>
                <a href="{{ route('vendor.payouts.index') }}" class="fc-card hover:border-tertiary/60">
                    <h3 class="font-semibold text-primary">Payouts</h3>
                    <p class="mt-1 text-sm text-primary/75">Review available balance and submit payout requests.</p>
                </a>
                @if ($canManageTeam)
                    <a href="{{ route('vendor.team.index') }}" class="fc-card hover:border-tertiary/60">
                        <h3 class="font-semibold text-primary">Manage Team</h3>
                        <p class="mt-1 text-sm text-primary/75">Create and deactivate vendor staff accounts.</p>
                    </a>
                @endif
            </div>

            <x-card title="Recent Bookings">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Booking</th>
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Client</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $booking->booking_no }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $booking->listing->title }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $booking->user->name }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $booking->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
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
            </x-card>

            @if ($tenant->status !== 'approved')
                <x-alert variant="warning">
                    Your vendor account is currently <strong>{{ $tenant->status }}</strong>. Complete your mini website profile and wait for admin approval.
                </x-alert>
            @endif

            @if ($tenant->profile)
                <x-card title="Public Vendor Page">
                    <p class="text-sm text-primary/75">This is the public mini website clients will see.</p>
                    <a href="{{ route('marketplace.vendor', $tenant->slug) }}" class="fc-btn fc-btn-outline mt-4">Preview Public Vendor Page</a>
                </x-card>
            @endif
        </div>
    </div>
</x-app-layout>
