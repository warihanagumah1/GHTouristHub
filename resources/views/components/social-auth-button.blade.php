@props([
    'provider' => 'google',
    'href' => '#',
    'label' => 'Continue',
])

@php
    $provider = strtolower((string) $provider);
@endphp

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'group flex w-full items-center gap-2 rounded-xl border border-slate-300 bg-slate-50 px-3 py-2 text-left text-base font-medium text-slate-700 transition hover:border-tertiary hover:bg-white hover:text-primary']) }}
>
    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-sm">
        @if ($provider === 'google')
            <svg class="h-5 w-5" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#FFC107" d="M43.6 20.5H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12S17.4 12 24 12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34 6.1 29.3 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.3-.4-3.5z"/>
                <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.7 15.3 19 12 24 12c3 0 5.7 1.1 7.8 2.9l5.7-5.7C34 6.1 29.3 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
                <path fill="#4CAF50" d="M24 44c5.2 0 9.8-2 13.3-5.1L31 33.6C29 35.1 26.6 36 24 36c-5.3 0-9.7-3.3-11.3-8l-6.5 5C9.5 39.5 16.2 44 24 44z"/>
                <path fill="#1976D2" d="M43.6 20.5H42V20H24v8h11.3c-1 2.7-2.7 4.8-4.9 6.3l.1.1 6.3 5.3C36.3 40 44 34 44 24c0-1.3-.1-2.3-.4-3.5z"/>
            </svg>
        @elseif ($provider === 'linkedin')
            <svg class="h-5 w-5 text-[#0A66C2]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.45 20.45h-3.56v-5.57c0-1.33-.03-3.03-1.84-3.03-1.84 0-2.13 1.44-2.13 2.93v5.67H9.36V9h3.42v1.56h.05c.48-.9 1.64-1.84 3.37-1.84 3.61 0 4.28 2.38 4.28 5.48v6.25zM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/>
            </svg>
        @endif
    </span>
    <span class="leading-none">{{ $label }}</span>
</a>
