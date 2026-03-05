<x-layouts.public>
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-primary">Tourist Attractions by Region</h1>
            <p class="mt-2 text-sm text-primary/75">Explore potential attraction sites across all regions in Ghana.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($regions as $region)
                <a href="{{ route('marketplace.attractions.region', $region->slug) }}" class="block">
                    <x-card class="h-full border-slate-200 transition hover:-translate-y-0.5 hover:border-secondary/60">
                        <h2 class="text-xl font-semibold text-primary">{{ $region->name }}</h2>
                        <p class="mt-2 text-sm text-primary/75">{{ $region->overview ?: 'Regional tourism highlights and potential attraction sites.' }}</p>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-secondary">
                            {{ (int) $region->attractions_count }} attraction{{ (int) $region->attractions_count === 1 ? '' : 's' }}
                        </p>
                        <p class="mt-2 text-sm font-semibold text-primary/80">View region attractions →</p>
                    </x-card>
                </a>
            @empty
                <x-alert variant="info" class="sm:col-span-2 lg:col-span-3">
                    No regions published yet.
                </x-alert>
            @endforelse
        </div>
    </section>
</x-layouts.public>
