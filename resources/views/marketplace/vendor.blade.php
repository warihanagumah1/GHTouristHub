<x-layouts.public>
    @php
        $profile = $tenant->profile;
        $companyReviewCount = (int) ($tenantReviewCount ?? 0);
        $companyRatingAverage = round((float) ($tenantRatingAverage ?? 0), 1);
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="fc-link">← Back to marketplace</a>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="relative h-56 bg-gradient-to-r from-primary to-secondary">
                <img
                    src="{{ $profile?->banner_url ?: 'https://images.unsplash.com/photo-1527631746610-bca00a040d60?auto=format&fit=crop&w=1400&q=80' }}"
                    alt="{{ $tenant->name }} banner"
                    class="h-full w-full object-cover opacity-65"
                />
                <div class="absolute inset-0 bg-gradient-to-r from-primary/70 to-secondary/50"></div>
                <div class="absolute bottom-5 left-5 flex items-end gap-3">
                    <img
                        src="{{ $profile?->logo_url ?: 'https://images.unsplash.com/photo-1573496799515-eebbb63814f2?auto=format&fit=crop&w=300&q=80' }}"
                        alt="{{ $tenant->name }}"
                        class="h-20 w-20 rounded-full border-4 border-white object-cover"
                    />
                    <div>
                        <h1 class="text-2xl font-bold text-white">{{ $tenant->name }}</h1>
                        <p class="text-sm text-white/85 capitalize">{{ str_replace('_', ' ', $tenant->type) }} • {{ $profile?->country }}</p>
                        <p class="mt-1 text-xs text-white/85">
                            {{ number_format($companyRatingAverage, 1) }}/5 ({{ $companyReviewCount }} review{{ $companyReviewCount === 1 ? '' : 's' }})
                        </p>
                    </div>
                </div>
            </div>
            <div class="grid gap-6 p-6 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    <h2 class="text-xl font-semibold text-primary">About the Company</h2>
                    <p class="mt-3 whitespace-pre-line text-sm leading-7 text-primary/80">
                        {{ $profile?->about ?: 'This vendor has not provided a full profile yet. Contact details are shown for direct questions.' }}
                    </p>
                </div>
                <x-card title="Company details" class="bg-slate-50">
                    <div class="space-y-2 text-sm text-primary/80">
                        <p><span class="font-semibold">City:</span> {{ $profile?->city ?: 'N/A' }}</p>
                        <p><span class="font-semibold">Country:</span> {{ $profile?->country ?: 'N/A' }}</p>
                        <p><span class="font-semibold">Founded:</span> {{ $profile?->founded_year ?: 'N/A' }}</p>
                        <p><span class="font-semibold">Email:</span> {{ $profile?->support_email ?: 'N/A' }}</p>
                        <p><span class="font-semibold">Phone:</span> {{ $profile?->support_phone ?: 'N/A' }}</p>
                        @if ($profile?->website_url)
                            <p><span class="font-semibold">Website:</span> <a class="fc-link" href="{{ $profile->website_url }}" target="_blank" rel="noreferrer">Visit site</a></p>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="mb-4 text-2xl font-semibold text-primary">Listings by {{ $tenant->name }}</h2>
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($tenant->listings as $listing)
                    <x-card class="overflow-hidden p-0">
                        <img
                            src="{{ $listing->coverMedia?->url ?? $listing->media->first()?->url ?? 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80' }}"
                            alt="{{ $listing->title }}"
                            class="h-44 w-full object-cover"
                        />
                        <div class="p-4">
                            <x-badge :variant="$listing->type === 'tour' ? 'primary' : 'tertiary'" class="mb-2 capitalize">
                                {{ $listing->type }}
                            </x-badge>
                            <h3 class="text-base font-semibold text-primary">{{ $listing->title }}</h3>
                            <p class="mt-1 text-sm text-primary/70">{{ $listing->city }}, {{ $listing->country }}</p>
                            <p class="mt-1 text-xs text-primary/65">
                                {{ number_format((float) $listing->rating_average, 1) }}/5 ({{ (int) $listing->rating_count }} review{{ (int) $listing->rating_count === 1 ? '' : 's' }})
                            </p>
                            <a href="{{ route('marketplace.listing', $listing->slug) }}" class="fc-link mt-3 inline-block">View details</a>
                        </div>
                    </x-card>
                @empty
                    <x-alert variant="info" class="md:col-span-2 lg:col-span-3">
                        This vendor has no published listings yet.
                    </x-alert>
                @endforelse
            </div>
        </div>

        <div class="mt-8">
            <h2 class="mb-4 text-2xl font-semibold text-primary">Customer Reviews</h2>
            <x-card>
                <p class="text-sm text-primary/75">
                    Company rating: <span class="font-semibold text-primary">{{ number_format($companyRatingAverage, 1) }}/5</span>
                    • {{ $companyReviewCount }} review{{ $companyReviewCount === 1 ? '' : 's' }}
                </p>
                <div class="mt-4 space-y-3">
                    @forelse ($tenantReviews as $review)
                        <div class="rounded-lg border border-slate-200 p-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-primary">{{ $review->user?->name ?: 'Verified traveler' }}</p>
                                <p class="text-xs text-primary/70">
                                    {{ $review->rating }}/5 • {{ $review->created_at->diffForHumans() }}
                                </p>
                            </div>
                            <p class="mt-1 text-xs text-primary/65">
                                For
                                @if ($review->listing)
                                    <a href="{{ route('marketplace.listing', $review->listing->slug) }}" class="fc-link">{{ $review->listing->title }}</a>
                                @else
                                    Listing
                                @endif
                            </p>
                            <p class="mt-2 text-sm text-primary/80">
                                {{ $review->comment ?: 'No written comment provided.' }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-primary/75">No customer reviews yet for this company.</p>
                    @endforelse
                </div>
            </x-card>
        </div>
    </section>
</x-layouts.public>
