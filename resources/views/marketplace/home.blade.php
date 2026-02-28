<x-layouts.public>
    <section class="relative flex min-h-[calc(100svh-88px)] items-center overflow-hidden bg-primary text-white">
        <div class="absolute inset-0">
            <div class="absolute -left-10 bottom-10 h-44 w-44 rounded-tr-[4rem] rounded-bl-[4rem] bg-secondary/85"></div>
            <div class="absolute -right-16 top-16 h-56 w-56 rounded-tl-[4rem] rounded-bl-[4rem] bg-secondary/85"></div>
        </div>

        <div class="relative mx-auto grid w-full max-w-7xl gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-24">
            <div>
                <x-badge variant="tertiary" class="mb-5 bg-white/20 text-white">
                    Discover. Book. Travel confidently.
                </x-badge>
                <h1 class="max-w-2xl text-4xl font-bold leading-tight sm:text-5xl">
                    Reach unforgettable trips and trusted services across top destinations.
                </h1>
                <p class="mt-5 max-w-xl text-lg text-white/80">
                    Book curated tours, hotels, transport, attractions, and event spaces from verified owners on {{ config('app.name', 'GH Tourist Hub') }}.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button-link :href="route('marketplace.tours')" variant="secondary">Explore tours</x-button-link>
                    <x-button-link :href="route('marketplace.utilities')" variant="outline" class="border-white hover:bg-white/90">Browse utilities</x-button-link>
                </div>
            </div>

            @php
                $heroSlides = $heroListings->values();
            @endphp
            <div
                class="relative"
                x-data="{
                    current: 0,
                    total: {{ $heroSlides->count() }},
                    timer: null,
                    startAutoplay() {
                        if (this.total < 2) return;
                        this.stopAutoplay();
                        this.timer = setInterval(() => this.next(), 6000);
                    },
                    stopAutoplay() {
                        if (this.timer) {
                            clearInterval(this.timer);
                            this.timer = null;
                        }
                    },
                    next() {
                        this.current = (this.current + 1) % this.total;
                    },
                    prev() {
                        this.current = (this.current - 1 + this.total) % this.total;
                    },
                }"
                x-init="startAutoplay()"
                @mouseenter="stopAutoplay()"
                @mouseleave="startAutoplay()"
            >
                <div class="relative mx-auto w-full max-w-[34rem]">
                    <div class="absolute -right-14 top-16 hidden h-[21rem] w-[12rem] rounded-[5.5rem] bg-tertiary/95 md:block"></div>
                    <div class="absolute -left-12 bottom-14 hidden h-[13rem] w-[10rem] rounded-[4rem] bg-indigo-600/85 md:block"></div>

                    <div class="relative aspect-[5/4] w-full overflow-hidden rounded-[3.2rem] border-[18px] border-tertiary">
                        @forelse ($heroSlides as $index => $heroListing)
                            @php
                                $heroImage = $heroListing->coverMedia?->url
                                    ?? $heroListing->media?->first()?->url
                                    ?? 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80';
                            @endphp
                            <a
                                href="{{ route('marketplace.listing', $heroListing->slug) }}"
                                class="absolute inset-0 block"
                                x-cloak
                                x-show="current === {{ $index }}"
                                x-transition.opacity.duration.400ms
                            >
                                <img
                                    src="{{ $heroImage }}"
                                    alt="{{ $heroListing->title }}"
                                    class="h-full w-full rounded-[2.2rem] object-cover"
                                />
                            </a>
                        @empty
                            <img
                                src="https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80"
                                alt="Featured trip"
                                class="h-full w-full rounded-[2.2rem] object-cover"
                            />
                        @endforelse
                    </div>

                    @if ($heroSlides->count() > 1)
                        <div class="absolute inset-x-4 bottom-5 flex items-center justify-between">
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-primary backdrop-blur transition hover:bg-secondary/90"
                                @click.prevent="prev()"
                                aria-label="Previous featured listing"
                            >
                                <span aria-hidden="true">‹</span>
                            </button>
                            <button
                                type="button"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-primary backdrop-blur transition hover:bg-secondary/90"
                                @click.prevent="next()"
                                aria-label="Next featured listing"
                            >
                                <span aria-hidden="true">›</span>
                            </button>
                        </div>
                    @endif
                </div>

                @if ($heroSlides->isNotEmpty())
                    <div class="absolute -bottom-6 left-2 right-2 sm:right-auto sm:max-w-xs">
                        @foreach ($heroSlides as $index => $heroListing)
                            <a
                                href="{{ route('marketplace.listing', $heroListing->slug) }}"
                                class="block rounded-2xl bg-white p-4 text-primary shadow-xl transition hover:-translate-y-0.5"
                                x-cloak
                                x-show="current === {{ $index }}"
                                x-transition.opacity.duration.300ms
                            >
                                <p class="text-xs uppercase tracking-wider text-primary/60">Featured Listing</p>
                                <p class="mt-1 text-sm font-semibold">{{ $heroListing->title }}</p>
                                <p class="mt-1 text-xs text-primary/70">{{ $heroListing->city }}, {{ $heroListing->country }}</p>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($heroSlides->count() > 1)
                    <div class="mt-6 flex items-center justify-center gap-2">
                        @foreach ($heroSlides as $index => $heroListing)
                            <button
                                type="button"
                                class="h-2.5 rounded-full bg-white/40 transition"
                                :class="current === {{ $index }} ? 'w-8 bg-white' : 'w-2.5'"
                                @click.prevent="current = {{ $index }}"
                                aria-label="Go to featured listing {{ $index + 1 }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-primary">Featured Tours</h2>
            <a href="{{ route('marketplace.tours') }}" class="fc-link">View all tours</a>
        </div>
        <div class="grid gap-5 md:grid-cols-3">
            @forelse ($featuredTours as $listing)
                <x-card class="overflow-hidden p-0">
                    <img
                        src="{{ $listing->coverMedia?->url ?? $listing->media->first()?->url ?? 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=1200&q=80' }}"
                        alt="{{ $listing->title }}"
                        class="h-48 w-full object-cover"
                    />
                    <div class="p-5">
                        <x-badge variant="primary" class="mb-2">Tour</x-badge>
                        <h3 class="text-lg font-semibold text-primary">{{ $listing->title }}</h3>
                        <p class="mt-1 text-sm text-primary/70">{{ $listing->city }}, {{ $listing->country }} • {{ $listing->duration_label }}</p>
                        <p class="mt-1 text-xs text-primary/65">
                            {{ number_format((float) $listing->rating_average, 1) }}/5 ({{ (int) $listing->rating_count }} review{{ (int) $listing->rating_count === 1 ? '' : 's' }})
                        </p>
                        <p class="mt-3 text-sm text-primary/75">By {{ $listing->tenant->name }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <p class="text-sm font-semibold text-primary">
                                <x-money :amount="$listing->price_from" :from="$listing->currency_code" show-original />
                                <span class="text-xs text-primary/60">{{ $listing->pricing_unit }}</span>
                            </p>
                            <a href="{{ route('marketplace.listing', $listing->slug) }}" class="fc-btn fc-btn-secondary">Details</a>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-alert variant="info" class="md:col-span-3">
                    No featured tours yet. Seed or publish listings to populate this section.
                </x-alert>
            @endforelse
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h2 class="text-2xl font-semibold text-primary">Featured Utilities</h2>
                <a href="{{ route('marketplace.utilities') }}" class="fc-link">View all utilities</a>
            </div>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                @forelse ($featuredUtilities as $listing)
                    <x-card class="overflow-hidden p-0">
                        <img
                            src="{{ $listing->coverMedia?->url ?? $listing->media->first()?->url ?? 'https://images.unsplash.com/photo-1496417263034-38ec4f0b665a?auto=format&fit=crop&w=1200&q=80' }}"
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
                            <div class="mt-3 flex items-center justify-between gap-3">
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
                        No featured utilities yet. Seed or publish listings to populate this section.
                    </x-alert>
                @endforelse
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h2 class="mb-6 text-2xl font-semibold text-primary">Destination Highlights</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($destinations as $destination)
                <a href="{{ route('marketplace.tours', ['destination' => $destination->city]) }}" class="block">
                    <x-card class="border-secondary/20 bg-gradient-to-br from-white via-white to-secondary/5 transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-lg font-semibold text-primary">{{ $destination->city }}</p>
                        <p class="text-sm text-primary/70">{{ $destination->country }}</p>
                        <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-secondary">
                            {{ $destination->listings_count }} listings
                        </p>
                        <p class="mt-2 text-xs font-semibold text-primary/70">Explore destination →</p>
                    </x-card>
                </a>
            @empty
                <x-alert variant="info" class="sm:col-span-2 lg:col-span-3">
                    Destinations will appear once listings are published.
                </x-alert>
            @endforelse
        </div>
    </section>
</x-layouts.public>
