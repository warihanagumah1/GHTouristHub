@props(['title' => null, 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'fc-card']) }}>
    @if ($title || $subtitle)
        <header class="mb-4">
            @if ($title)
                <h3 class="text-lg font-semibold text-primary">{{ $title }}</h3>
            @endif
            @if ($subtitle)
                <p class="mt-1 text-sm text-primary/70">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</section>
