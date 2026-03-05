<x-layouts.public>
    @php
        $heroImage = $attraction->hero_image_url
            ?: (($attraction->gallery_images ?? [])[0] ?? 'https://picsum.photos/seed/attraction-'.$attraction->id.'/1400/900');
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-wrap items-center gap-2 text-sm text-primary/70">
            <a href="{{ route('marketplace.attractions.index') }}" class="fc-link">Tourist Attractions</a>
            <span>•</span>
            <a href="{{ route('marketplace.attractions.region', $region->slug) }}" class="fc-link">{{ $region->name }}</a>
            <span>•</span>
            <span class="text-primary">{{ $attraction->name }}</span>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <img src="{{ $heroImage }}" alt="{{ $attraction->name }}" class="h-[420px] w-full object-cover" />
            <div class="p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 class="text-3xl font-bold text-primary">{{ $attraction->name }}</h1>
                        <p class="mt-1 text-sm text-primary/70">{{ $attraction->city ?: $region->name }} • {{ $region->name }} Region</p>
                    </div>
                    @if ($attraction->is_featured)
                        <x-badge variant="secondary">Featured Attraction</x-badge>
                    @endif
                </div>
                @if ($attraction->summary)
                    <p class="mt-4 text-base text-primary/80">{{ $attraction->summary }}</p>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="About This Attraction">
                    <p class="whitespace-pre-line text-sm leading-7 text-primary/80">
                        {{ $attraction->description ?: 'This destination is one of the notable attraction sites in the region. Detailed historical, cultural, and visitor information will continue to be updated by the platform admin team.' }}
                    </p>
                </x-card>

                @if (! empty($attraction->featured_activities))
                    <x-card title="Top Activities">
                        <ul class="list-disc space-y-2 ps-5 text-sm text-primary/80">
                            @foreach ($attraction->featured_activities as $activity)
                                <li>{{ $activity }}</li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif

                <x-card title="How To Get There">
                    <p class="whitespace-pre-line text-sm text-primary/80">
                        {{ $attraction->how_to_get_there ?: 'Travel by road from the nearest major city. Use local tour guides or transport operators familiar with '.$region->name.' region for easier access and updated route conditions.' }}
                    </p>
                </x-card>

                <x-card title="Travel Tips">
                    <p class="whitespace-pre-line text-sm text-primary/80">
                        {{ $attraction->travel_tips ?: 'Visit in daylight, carry water, wear comfortable walking shoes, and check local weather before travel. Keep cash and digital payment options available for entry fees or local services.' }}
                    </p>
                </x-card>

                <x-card title="Safety Notes">
                    <p class="whitespace-pre-line text-sm text-primary/80">
                        {{ $attraction->safety_notes ?: 'Follow local guide instructions, keep valuables secure, and observe site rules. Children should be supervised, especially near water bodies, cliffs, and wildlife areas.' }}
                    </p>
                </x-card>

                @if (! empty($attraction->nearby_places))
                    <x-card title="Nearby Places">
                        <ul class="list-disc space-y-2 ps-5 text-sm text-primary/80">
                            @foreach ($attraction->nearby_places as $place)
                                <li>{{ $place }}</li>
                            @endforeach
                        </ul>
                    </x-card>
                @endif

                @if (! empty($attraction->gallery_images))
                    <x-card title="Photo Gallery">
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($attraction->gallery_images as $index => $image)
                                <img src="{{ $image }}" alt="{{ $attraction->name }} image {{ $index + 1 }}" class="h-44 w-full rounded-lg object-cover" />
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </div>

            <aside class="space-y-4">
                <x-card title="Visitor Information">
                    <div class="space-y-3 text-sm text-primary/80">
                        <p><span class="font-semibold">Region:</span> {{ $region->name }}</p>
                        <p><span class="font-semibold">City/Town:</span> {{ $attraction->city ?: 'To be confirmed' }}</p>
                        <p><span class="font-semibold">Address:</span> {{ $attraction->address ?: 'Detailed address available on request' }}</p>
                        <p><span class="font-semibold">Visiting Hours:</span> {{ $attraction->visiting_hours ?: 'Varies by season; confirm before visit' }}</p>
                        <p><span class="font-semibold">Entry Fee:</span> {{ $attraction->entry_fee ?: 'Contact site management' }}</p>
                        <p><span class="font-semibold">Best Time To Visit:</span> {{ $attraction->best_time_to_visit ?: 'Dry season and early mornings recommended' }}</p>
                        <p><span class="font-semibold">Contact:</span> {{ $attraction->contact_info ?: 'Support via GH Tourist Hub' }}</p>
                        @if ($attraction->website_url)
                            <p>
                                <span class="font-semibold">Website:</span>
                                <a href="{{ $attraction->website_url }}" class="fc-link" target="_blank" rel="noopener noreferrer">Visit website</a>
                            </p>
                        @else
                            <p><span class="font-semibold">Website:</span> Not available</p>
                        @endif
                    </div>
                </x-card>

                @if ($relatedAttractions->isNotEmpty())
                    <x-card title="More In {{ $region->name }}">
                        <div class="space-y-2">
                            @foreach ($relatedAttractions as $related)
                                <a href="{{ route('marketplace.attractions.show', [$region->slug, $related->slug]) }}" class="block rounded-lg border border-slate-200 px-3 py-2 text-sm text-primary/80 hover:border-secondary/50 hover:text-primary">
                                    {{ $related->name }}
                                </a>
                            @endforeach
                        </div>
                    </x-card>
                @endif
            </aside>
        </div>
    </section>
</x-layouts.public>
