<x-layouts.public>
    @php
        $gallery = $listing->media;
        $cover = $gallery->firstWhere('is_cover', true) ?? $gallery->first();
        $location = trim(($listing->city ? $listing->city.', ' : '').($listing->country ?? ''), ', ');
        $reviewCount = (int) ($listing->rating_count ?? 0);
        $ratingAverage = round((float) ($listing->rating_average ?? 0), 1);
        $reviews = $listing->reviews;
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ $listing->type === 'tour' ? route('marketplace.tours') : route('marketplace.utilities') }}" class="fc-link">
            ← Back to {{ $listing->type === 'tour' ? 'tours' : 'utilities' }}
        </a>

        <div class="mt-4 grid gap-8 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <img
                        src="{{ $cover?->url ?? 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?auto=format&fit=crop&w=1400&q=80' }}"
                        alt="{{ $cover?->alt_text ?? $listing->title }}"
                        class="h-[420px] w-full object-cover"
                    />
                    <div class="p-6">
                        <x-badge :variant="$listing->type === 'tour' ? 'primary' : 'tertiary'" class="mb-2 capitalize">
                            {{ $listing->type }} {{ $listing->subtype ? '• '.str_replace('_', ' ', $listing->subtype) : '' }}
                        </x-badge>
                        <h1 class="text-3xl font-bold text-primary">{{ $listing->title }}</h1>
                        <p class="mt-2 text-sm text-primary/70">
                            {{ $location ?: 'Location to be confirmed' }}
                            • {{ number_format($ratingAverage, 1) }}/5 ({{ $reviewCount }} review{{ $reviewCount === 1 ? '' : 's' }})
                        </p>
                        @if ($listing->summary)
                            <p class="mt-4 text-base text-primary/80">{{ $listing->summary }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <h2 class="mb-4 text-xl font-semibold text-primary">Images</h2>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($gallery as $media)
                            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                <img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $listing->title }}" class="h-44 w-full object-cover" />
                                @if ($media->caption)
                                    <p class="p-2 text-xs text-primary/70">{{ $media->caption }}</p>
                                @endif
                            </div>
                        @empty
                            <x-alert variant="info" class="sm:col-span-2 lg:col-span-3">
                                No gallery images available for this listing yet.
                            </x-alert>
                        @endforelse
                    </div>
                </div>

                <div class="mt-6 grid gap-6">
                    <x-card title="Overview">
                        <p class="whitespace-pre-line text-sm leading-7 text-primary/80">{{ $listing->description }}</p>
                    </x-card>

                    @if ($listing->type === 'tour')
                        <x-card title="Tour Highlights">
                            <ul class="list-disc space-y-2 pl-5 text-sm text-primary/80">
                                @forelse (($listing->highlights ?? []) as $item)
                                    <li>{{ $item }}</li>
                                @empty
                                    <li>Highlights will be updated by the tour company.</li>
                                @endforelse
                            </ul>
                        </x-card>

                        <x-card title="Itinerary">
                            <div class="space-y-3">
                                @forelse (($listing->itinerary ?? []) as $index => $day)
                                    <div class="rounded-lg border border-slate-200 p-3">
                                        <p class="text-sm font-semibold text-primary">Day {{ $index + 1 }}</p>
                                        <p class="mt-1 text-sm text-primary/75">{{ $day }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-primary/75">Detailed itinerary will be shared after booking.</p>
                                @endforelse
                            </div>
                        </x-card>

                        <div class="grid gap-4 md:grid-cols-2">
                            <x-card title="Inclusions">
                                <ul class="list-disc space-y-2 pl-5 text-sm text-primary/80">
                                    @forelse (($listing->inclusions ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Inclusions not specified.</li>
                                    @endforelse
                                </ul>
                            </x-card>
                            <x-card title="Exclusions">
                                <ul class="list-disc space-y-2 pl-5 text-sm text-primary/80">
                                    @forelse (($listing->exclusions ?? []) as $item)
                                        <li>{{ $item }}</li>
                                    @empty
                                        <li>Exclusions not specified.</li>
                                    @endforelse
                                </ul>
                            </x-card>
                        </div>
                    @else
                        <x-card title="Amenities and Facilities">
                            <ul class="grid gap-2 sm:grid-cols-2">
                                @forelse (($listing->amenities ?? []) as $amenity)
                                    <li class="rounded-md bg-slate-50 px-3 py-2 text-sm text-primary/80">{{ $amenity }}</li>
                                @empty
                                    <li class="text-sm text-primary/75">Amenities will be updated by the owner.</li>
                                @endforelse
                            </ul>
                        </x-card>
                    @endif

                    <div class="grid gap-4 md:grid-cols-2">
                        <x-card title="Booking Rules">
                            <p class="text-sm text-primary/80">{{ $listing->booking_rules ?: 'Booking cutoff, check-in, and traveler rules will be provided during checkout.' }}</p>
                        </x-card>
                        <x-card title="Cancellation Policy">
                            <p class="text-sm text-primary/80">{{ $listing->cancellation_policy ?: 'Cancellation policy available on request.' }}</p>
                        </x-card>
                    </div>

                    <x-card title="Customer Reviews">
                        <p class="text-sm text-primary/75">
                            Rating: <span class="font-semibold text-primary">{{ number_format($ratingAverage, 1) }}/5</span>
                            • {{ $reviewCount }} review{{ $reviewCount === 1 ? '' : 's' }}
                        </p>
                        <div class="mt-4 space-y-3">
                            @forelse ($reviews as $review)
                                <div class="rounded-lg border border-slate-200 p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-semibold text-primary">{{ $review->user?->name ?: 'Verified traveler' }}</p>
                                        <p class="text-xs text-primary/70">
                                            {{ $review->rating }}/5 • {{ $review->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <p class="mt-2 text-sm text-primary/80">
                                        {{ $review->comment ?: 'No written comment provided.' }}
                                    </p>
                                </div>
                            @empty
                                <p class="text-sm text-primary/75">No reviews yet for this listing.</p>
                            @endforelse
                        </div>
                    </x-card>
                </div>
            </div>

            <aside class="space-y-4">
                <x-card>
                    <p class="text-xs uppercase tracking-wider text-primary/60">Starting price</p>
                    <p class="mt-1 text-3xl font-bold text-primary">
                        <x-money :amount="$listing->price_from" :from="$listing->currency_code" show-original />
                    </p>
                    <p class="mt-1 text-sm text-primary/70">{{ $listing->pricing_unit ?: 'Per booking' }}</p>
                    <p class="mt-1 text-xs text-primary/60">Checkout is charged in {{ strtoupper((string) ($listing->currency_code ?: 'USD')) }}.</p>

                    <div class="mt-5 grid gap-3 rounded-lg bg-slate-50 p-3 text-sm text-primary/80">
                        @if ($listing->duration_label)
                            <p><span class="font-semibold">Duration:</span> {{ $listing->duration_label }}</p>
                        @endif
                        @if ($listing->group_size_label)
                            <p><span class="font-semibold">Group size:</span> {{ $listing->group_size_label }}</p>
                        @endif
                        @if (! empty($listing->languages))
                            <p><span class="font-semibold">Languages:</span> {{ implode(', ', $listing->languages) }}</p>
                        @endif
                        @if ($location)
                            <p><span class="font-semibold">Location:</span> {{ $location }}</p>
                        @endif
                    </div>

                    <div class="mt-5 space-y-2">
                        @auth
                            @if (auth()->user()->user_role === \App\Models\User::ROLE_CLIENT)
                                <form method="POST" action="{{ route('client.bookings.store', $listing) }}" x-data="{ quantity: {{ max(1, (int) old('travelers_count', 1)) }}, unitPrice: {{ (float) $listing->price_from }} }">
                                    @csrf
                                    @if ($errors->any())
                                        <x-alert variant="danger" class="mb-3">{{ $errors->first() }}</x-alert>
                                    @endif
                                    <div>
                                        <x-input-label for="travelers_count" value="Quantity" />
                                        <x-text-input
                                            id="travelers_count"
                                            name="travelers_count"
                                            type="number"
                                            min="1"
                                            max="50"
                                            step="1"
                                            x-model.number="quantity"
                                            class="mt-1 w-full"
                                            required
                                        />
                                        <p class="mt-1 text-xs text-primary/60">Enter number of travelers/tickets (1 to 50).</p>
                                        <p class="mt-1 text-sm font-semibold text-primary">
                                            Estimated total:
                                            <span x-text="(Math.max(1, quantity || 1) * unitPrice).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                            {{ strtoupper((string) ($listing->currency_code ?: 'USD')) }}
                                        </p>
                                    </div>
                                    <div>
                                        <x-input-label for="special_requests" value="Special Requests (optional)" />
                                        <x-textarea-input
                                            id="special_requests"
                                            name="special_requests"
                                            class="mt-1"
                                            rows="3"
                                            maxlength="1000"
                                            placeholder="Any date preference, pickup point, dietary notes, or accessibility needs..."
                                        >{{ old('special_requests') }}</x-textarea-input>
                                        <p class="mt-1 text-xs text-primary/60">Up to 1000 characters.</p>
                                    </div>
                                    <x-button type="submit" variant="secondary" class="w-full">Start booking</x-button>
                                </form>
                            @else
                                <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="fc-btn fc-btn-secondary w-full text-center">
                                    Go to Dashboard
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="fc-btn fc-btn-secondary w-full text-center">Log in to Book</a>
                        @endauth
                        <a href="{{ route('marketplace.vendor', $listing->tenant->slug) }}" class="fc-btn fc-btn-outline w-full">
                            View Owner Details
                        </a>
                    </div>
                </x-card>

                <x-card title="Hosted by">
                    <div class="flex items-start gap-3">
                        <img
                            src="{{ $listing->tenant->profile?->logo_url ?: 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=400&q=80' }}"
                            alt="{{ $listing->tenant->name }}"
                            class="h-14 w-14 rounded-full object-cover"
                        />
                        <div>
                            <p class="text-sm font-semibold text-primary">{{ $listing->tenant->name }}</p>
                            <p class="text-xs text-primary/70 capitalize">{{ str_replace('_', ' ', $listing->tenant->type) }}</p>
                            <p class="mt-2 text-xs text-primary/75">
                                {{ $listing->tenant->profile?->about ?: 'Verified service provider on '.config('app.name', 'GH Tourist Hub').'.' }}
                            </p>
                            <a href="{{ route('marketplace.vendor', $listing->tenant->slug) }}" class="fc-btn fc-btn-outline mt-3 w-full text-center">
                                Learn More
                            </a>
                        </div>
                    </div>
                </x-card>
            </aside>
        </div>
    </section>
</x-layouts.public>
