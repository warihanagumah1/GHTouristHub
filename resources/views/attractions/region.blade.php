<x-layouts.public>
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <a href="{{ route('marketplace.attractions.index') }}" class="fc-link">← All regions</a>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-6">
            <h1 class="text-3xl font-bold text-primary">{{ $region->name }} Region Attractions</h1>
            <p class="mt-3 text-sm text-primary/75">{{ $region->overview ?: 'Potential tourist attractions and destination highlights.' }}</p>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            @forelse ($region->attractions as $attraction)
                @php
                    $cardImage = $attraction->hero_image_url
                        ?: (($attraction->gallery_images ?? [])[0] ?? 'https://picsum.photos/seed/attraction-'.$attraction->id.'/900/600');
                @endphp
                <x-card class="overflow-hidden border-slate-200 p-0">
                    <img src="{{ $cardImage }}" alt="{{ $attraction->name }}" class="h-52 w-full object-cover" />
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <h2 class="text-lg font-semibold text-primary">{{ $attraction->name }}</h2>
                            @if ($attraction->is_featured)
                                <x-badge variant="secondary">Featured</x-badge>
                            @endif
                        </div>
                        @if ($attraction->city)
                            <p class="mt-1 text-sm text-primary/65">{{ $attraction->city }}</p>
                        @endif
                        @if ($attraction->summary)
                            <p class="mt-3 text-sm text-primary/80">{{ $attraction->summary }}</p>
                        @endif
                        @if ($attraction->description)
                            <p class="mt-3 text-sm text-primary/75">{{ \Illuminate\Support\Str::limit($attraction->description, 180) }}</p>
                        @endif
                        <a href="{{ route('marketplace.attractions.show', [$region->slug, $attraction->slug]) }}" class="fc-btn fc-btn-secondary mt-4 inline-flex">
                            View Full Details
                        </a>
                    </div>
                </x-card>
            @empty
                <x-alert variant="info" class="md:col-span-2">
                    No attractions published for this region yet.
                </x-alert>
            @endforelse
        </div>
    </section>
</x-layouts.public>
