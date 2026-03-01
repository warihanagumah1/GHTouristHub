<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <x-alert variant="success">{{ session('status') }}</x-alert>
            @endif

            <div class="grid gap-4 md:grid-cols-4">
                <x-stat-card label="Pending Vendors" :value="$stats['pending_vendors']" />
                <x-stat-card label="Approved Vendors" :value="$stats['approved_vendors']" />
                <x-stat-card label="Published Listings" :value="$stats['published_listings']" />
                <x-stat-card label="Bookings (30d)" :value="$stats['bookings_30d']" />
                <x-stat-card label="GMV (30d USD)" :value="'$'.number_format($stats['gmv_30d_usd'], 2)" />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <x-card title="User Activity">
                    <p class="text-sm text-primary/80">Total users: <strong>{{ $stats['total_users'] }}</strong></p>
                    <p class="text-sm text-primary/80">Active clients: <strong>{{ $stats['active_clients'] }}</strong></p>
                    <p class="mt-1 text-sm text-primary/80">Active vendor users: <strong>{{ $stats['active_vendors'] }}</strong></p>
                </x-card>
                <x-card title="Operations">
                    <p class="text-sm text-primary/80">Total listings: <strong>{{ $stats['total_listings'] }}</strong></p>
                    <div class="mt-4 grid gap-2 md:grid-cols-5">
                        <a href="{{ route('admin.users.index') }}" class="fc-btn fc-btn-outline text-center text-[10px]">Manage Users</a>
                        <a href="{{ route('admin.listings.index') }}" class="fc-btn fc-btn-outline text-center text-[10px]">Moderate Listings</a>
                        <a href="{{ route('admin.analytics') }}" class="fc-btn fc-btn-secondary text-center text-[10px]">Analytics</a>
                        <a href="{{ route('admin.payouts.index') }}" class="fc-btn fc-btn-outline text-center text-[10px]">Payouts</a>
                        <a href="{{ route('admin.support-tickets.index') }}" class="fc-btn fc-btn-outline text-center text-[10px]">Support Tickets</a>
                    </div>
                </x-card>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <x-card title="Recent Users (Quick Actions)">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-primary/70">
                                    <th class="py-2 pe-4">User</th>
                                    <th class="py-2 pe-4">Role</th>
                                    <th class="py-2 pe-4">State</th>
                                    <th class="py-2 pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentUsers as $user)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pe-4">
                                            <p class="font-medium text-primary">{{ $user->name }}</p>
                                            <p class="text-xs text-primary/70">{{ $user->email }}</p>
                                        </td>
                                        <td class="py-2 pe-4 text-primary/75 capitalize">{{ str_replace('_', ' ', (string) $user->user_role) }}</td>
                                        <td class="py-2 pe-4">
                                            <x-badge :variant="$user->is_blocked ? 'secondary' : 'primary'">
                                                {{ $user->is_blocked ? 'Blocked' : 'Active' }}
                                            </x-badge>
                                        </td>
                                        <td class="py-2 pe-4">
                                            @if (auth()->id() === $user->id && ! $user->is_blocked)
                                                <x-button type="button" variant="outline" class="text-[10px]" disabled>
                                                    Block
                                                </x-button>
                                            @else
                                                @php
                                                    $shouldBlock = ! $user->is_blocked;
                                                    $actionLabel = $shouldBlock ? 'Block' : 'Unblock';
                                                    $dialogTitle = $shouldBlock ? 'Block User Account' : 'Unblock User Account';
                                                    $dialogMessage = $shouldBlock
                                                        ? "Block {$user->email}? They will immediately lose access until unblocked."
                                                        : "Unblock {$user->email}? They will be able to sign in again.";
                                                @endphp
                                                <x-confirm-action-form
                                                    :name="'confirm-admin-dashboard-user-block-'.$user->id"
                                                    :action="route('admin.users.block', $user)"
                                                    method="PUT"
                                                    :title="$dialogTitle"
                                                    :message="$dialogMessage"
                                                    :trigger-label="$actionLabel"
                                                    :trigger-class="'fc-btn '.($shouldBlock ? 'fc-btn-danger' : 'fc-btn-secondary').' text-[10px]'"
                                                    :confirm-label="$actionLabel"
                                                >
                                                    <input type="hidden" name="is_blocked" value="{{ $shouldBlock ? 1 : 0 }}">
                                                </x-confirm-action-form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-4 text-center text-primary/70">No users available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <x-card title="Recent Listings (Quick Actions)">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-primary/70">
                                    <th class="py-2 pe-4">Listing</th>
                                    <th class="py-2 pe-4">Status</th>
                                    <th class="py-2 pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentListings as $listing)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-2 pe-4">
                                            <p class="font-medium text-primary">{{ $listing->title }}</p>
                                            <p class="text-xs text-primary/70">{{ $listing->tenant?->name }} • {{ ucfirst((string) $listing->type) }}</p>
                                        </td>
                                        <td class="py-2 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', (string) $listing->status) }}</td>
                                        <td class="py-2 pe-4">
                                            @php
                                                $shouldBlock = $listing->status !== 'blocked';
                                                $actionLabel = $shouldBlock ? 'Block' : 'Unblock';
                                                $dialogTitle = $shouldBlock ? 'Block Listing' : 'Unblock Listing';
                                                $dialogMessage = $shouldBlock
                                                    ? "Block \"{$listing->title}\"? It will be hidden from customers immediately."
                                                    : "Unblock \"{$listing->title}\"? It will be moved to paused status for vendor review.";
                                            @endphp
                                            <x-confirm-action-form
                                                :name="'confirm-admin-dashboard-listing-block-'.$listing->id"
                                                :action="route('admin.listings.block', $listing)"
                                                method="PUT"
                                                :title="$dialogTitle"
                                                :message="$dialogMessage"
                                                :trigger-label="$actionLabel"
                                                :trigger-class="'fc-btn '.($shouldBlock ? 'fc-btn-danger' : 'fc-btn-secondary').' text-[10px]'"
                                                :confirm-label="$actionLabel"
                                            >
                                                <input type="hidden" name="is_blocked" value="{{ $shouldBlock ? 1 : 0 }}">
                                            </x-confirm-action-form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-4 text-center text-primary/70">No listings available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            <x-card title="Recent Bookings">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Booking</th>
                                <th class="py-2 pe-4">Client</th>
                                <th class="py-2 pe-4">Vendor</th>
                                <th class="py-2 pe-4">Listing</th>
                                <th class="py-2 pe-4">Status</th>
                                <th class="py-2 pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentBookings as $booking)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 font-medium text-primary">{{ $booking->booking_no }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $booking->user->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $booking->tenant->name }}</td>
                                    <td class="py-3 pe-4 text-primary/75">{{ $booking->listing->title }}</td>
                                    <td class="py-3 pe-4 capitalize text-primary/75">{{ str_replace('_', ' ', $booking->status) }}</td>
                                    <td class="py-3 pe-4 text-primary/80">
                                        <x-money :amount="$booking->total_amount" :from="$booking->currency" show-original />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-primary/70">No bookings available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
