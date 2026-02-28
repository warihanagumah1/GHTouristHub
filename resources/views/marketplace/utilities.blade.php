<x-layouts.public>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $listCta = null;
            if (auth()->check()) {
                $role = auth()->user()->user_role;
                if (in_array($role, [\App\Models\User::ROLE_UTILITY_OWNER, \App\Models\User::ROLE_UTILITY_STAFF], true)) {
                    $listCta = ['href' => route('vendor.listings.create', ['type' => 'utility']), 'label' => 'List utility'];
                } elseif (in_array($role, [\App\Models\User::ROLE_TOUR_OWNER, \App\Models\User::ROLE_TOUR_STAFF], true)) {
                    $listCta = ['href' => route('vendor.listings.create', ['type' => 'tour']), 'label' => 'List company tour'];
                }
            }
        @endphp
        <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-primary">Browse Utilities</h1>
                <p class="mt-2 text-sm text-primary/75">Hotels, transport, attractions, and event venues from trusted owners.</p>
            </div>
            @if ($listCta)
                <a href="{{ $listCta['href'] }}" class="fc-btn fc-btn-secondary">{{ $listCta['label'] }}</a>
            @endif
        </div>

        <form method="GET" class="mb-8 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-5">
            <x-text-input name="q" :value="$filters['q']" type="text" placeholder="Search utilities..." />
            <x-text-input name="destination" :value="$filters['destination']" type="text" placeholder="Destination (city/country)" />
            <x-select-input name="subtype">
                <option value="">All utility types</option>
                @foreach ($subtypes as $subtype)
                    <option value="{{ $subtype }}" @selected($filters['subtype'] === $subtype)>{{ ucfirst(str_replace('_', ' ', $subtype)) }}</option>
                @endforeach
            </x-select-input>
            <x-select-input name="sort">
                <option value="newest" @selected($filters['sort'] === 'newest')>Sort by newest</option>
                <option value="price_low" @selected($filters['sort'] === 'price_low')>Price: low to high</option>
                <option value="price_high" @selected($filters['sort'] === 'price_high')>Price: high to low</option>
                <option value="rating" @selected($filters['sort'] === 'rating')>Top rated</option>
            </x-select-input>
            <div class="flex gap-2">
                <x-button type="submit" variant="secondary" class="flex-1">Search</x-button>
                <a href="{{ route('marketplace.utilities') }}" class="fc-btn fc-btn-outline">Reset</a>
            </div>
        </form>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
            @forelse ($utilities as $listing)
                <x-card class="overflow-hidden p-0">
                    <img
                        src="{{ $listing->coverMedia?->url ?? $listing->media->first()?->url ?? 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?auto=format&fit=crop&w=1200&q=80' }}"
                        alt="{{ $listing->title }}"
                        class="h-36 w-full object-cover"
                    />
                    <div class="p-4">
                        <x-badge variant="tertiary" class="mb-2 capitalize">{{ str_replace('_', ' ', (string) $listing->subtype) }}</x-badge>
                        <h3 class="text-base font-semibold text-primary">{{ $listing->title }}</h3>
                        <p class="mt-1 text-sm text-primary/70">{{ $listing->city }}, {{ $listing->country }}</p>
                        <p class="mt-1 text-xs text-primary/65">
                            {{ number_format((float) $listing->rating_average, 1) }}/5 ({{ (int) $listing->rating_count }} review{{ (int) $listing->rating_count === 1 ? '' : 's' }})
                        </p>
                        <p class="mt-2 text-xs text-primary/60">By {{ $listing->tenant->name }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm text-primary/75">
                                <x-money :amount="$listing->price_from" :from="$listing->currency_code" show-original />
                                • {{ $listing->pricing_unit }}
                            </p>
                            <a href="{{ route('marketplace.listing', $listing->slug) }}" class="fc-btn fc-btn-secondary">View</a>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-alert variant="info" class="md:col-span-2 lg:col-span-4">
                    No published utility listings available yet.
                </x-alert>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $utilities->links() }}
        </div>
    </section>
</x-layouts.public>
