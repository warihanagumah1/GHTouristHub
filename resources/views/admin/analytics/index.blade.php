<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">Admin • Analytics</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <x-alert variant="danger">{{ $errors->first() }}</x-alert>
            @endif

            <x-card title="Analytics Filters">
                <form method="GET" class="grid gap-3 md:grid-cols-6">
                    <div>
                        <x-input-label for="date_from" value="From" />
                        <x-text-input id="date_from" name="date_from" type="date" class="mt-1" :value="$filters['date_from']" />
                    </div>
                    <div>
                        <x-input-label for="date_to" value="To" />
                        <x-text-input id="date_to" name="date_to" type="date" class="mt-1" :value="$filters['date_to']" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Booking Status" />
                        <x-select-input id="status" name="status" class="mt-1">
                            <option value="">All</option>
                            @foreach (['pending_payment', 'paid', 'confirmed', 'cancelled', 'completed'] as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="type" value="Listing Type" />
                        <x-select-input id="type" name="type" class="mt-1">
                            <option value="">All</option>
                            <option value="tour" @selected($filters['type'] === 'tour')>Tour</option>
                            <option value="utility" @selected($filters['type'] === 'utility')>Utility</option>
                        </x-select-input>
                    </div>
                    <div>
                        <x-input-label for="tenant_id" value="Vendor" />
                        <x-select-input id="tenant_id" name="tenant_id" class="mt-1">
                            <option value="">All Vendors</option>
                            @foreach ($tenants as $tenant)
                                <option value="{{ $tenant->id }}" @selected($filters['tenant_id'] === (string) $tenant->id)>{{ $tenant->name }}</option>
                            @endforeach
                        </x-select-input>
                    </div>
                    <div class="flex items-end">
                        <x-button type="submit" variant="secondary" class="w-full">Apply</x-button>
                    </div>
                </form>
            </x-card>

            <div class="grid gap-4 md:grid-cols-4">
                <x-stat-card label="GMV (USD)" :value="'$'.number_format((float) $gmvUsd30d, 2)" />
                <x-stat-card label="Bookings (Filtered)" :value="$dailyTrend->sum('bookings')" />
                <x-stat-card label="Top Destinations Tracked" :value="$topDestinations->count()" />
                <x-stat-card label="Top Vendors Tracked" :value="$topVendors->count()" />
            </div>

            @php
                $maxBookings = max(1, (int) $dailyTrend->max('bookings'));
                $maxRevenue = max(1, (float) $dailyTrend->max('revenue_usd'));
            @endphp

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Bookings Trend">
                    <div class="h-44">
                        <div class="flex h-full items-end gap-1">
                            @foreach ($dailyTrend as $point)
                                @php
                                    $height = max(4, (int) round(($point['bookings'] / $maxBookings) * 100));
                                @endphp
                                <div class="group flex-1">
                                    <div class="w-full rounded-t bg-primary/80 transition group-hover:bg-primary" style="height: {{ $height }}%"></div>
                                    <p class="mt-1 truncate text-center text-[10px] text-primary/60">{{ \Illuminate\Support\Carbon::parse($point['date'])->format('m/d') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>

                <x-card title="Revenue Trend (USD)">
                    <div class="h-44">
                        <div class="flex h-full items-end gap-1">
                            @foreach ($dailyTrend as $point)
                                @php
                                    $height = max(4, (int) round(($point['revenue_usd'] / $maxRevenue) * 100));
                                @endphp
                                <div class="group flex-1">
                                    <div class="w-full rounded-t bg-tertiary/90 transition group-hover:bg-tertiary" style="height: {{ $height }}%"></div>
                                    <p class="mt-1 truncate text-center text-[10px] text-primary/60">{{ \Illuminate\Support\Carbon::parse($point['date'])->format('m/d') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Bookings by Status">
                    @foreach ($bookingsByStatus as $status => $total)
                        @php
                            $percentage = $dailyTrend->sum('bookings') > 0 ? round(($total / max(1, $dailyTrend->sum('bookings'))) * 100, 1) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="mb-1 flex items-center justify-between text-sm text-primary/80">
                                <span class="capitalize">{{ str_replace('_', ' ', (string) $status) }}</span>
                                <span>{{ $total }}</span>
                            </div>
                            <div class="h-2 rounded bg-slate-100">
                                <div class="h-2 rounded bg-primary" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </x-card>

                <x-card title="Paid Revenue by Currency">
                    <div class="space-y-2 text-sm">
                        @forelse ($currencyBreakdown as $item)
                            <p class="text-primary/80">
                                <span class="font-semibold">{{ $item->currency }}:</span>
                                {{ $currencyService->symbol((string) $item->currency) }}{{ number_format((float) $item->total, 2) }}
                            </p>
                        @empty
                            <p class="text-primary/70">No paid revenue in selected range.</p>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Top Destinations">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-primary/70">
                                    <th class="py-2 pe-4">Destination</th>
                                    <th class="py-2 pe-4">Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topDestinations as $item)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-3 pe-4 text-primary">{{ $item->city }}, {{ $item->country }}</td>
                                        <td class="py-3 pe-4 text-primary/80">{{ $item->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-6 text-center text-primary/70">No destination analytics yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>

                <x-card title="Top Vendors">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-slate-200 text-left text-primary/70">
                                    <th class="py-2 pe-4">Vendor</th>
                                    <th class="py-2 pe-4">Bookings</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topVendors as $vendor)
                                    <tr class="border-b border-slate-100">
                                        <td class="py-3 pe-4 text-primary">{{ $vendor->name }}</td>
                                        <td class="py-3 pe-4 text-primary/80">{{ $vendor->bookings_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="py-6 text-center text-primary/70">No vendor analytics yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-card>
            </div>

            <x-card title="Monthly User Growth">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-primary/70">
                                <th class="py-2 pe-4">Month</th>
                                <th class="py-2 pe-4">New Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($monthlyUsers as $bucket)
                                <tr class="border-b border-slate-100">
                                    <td class="py-3 pe-4 text-primary">{{ $bucket->bucket }}</td>
                                    <td class="py-3 pe-4 text-primary/80">{{ $bucket->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="py-6 text-center text-primary/70">No user growth data yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </div>
</x-app-layout>
