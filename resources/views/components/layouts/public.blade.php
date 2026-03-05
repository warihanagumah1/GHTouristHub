<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'GH Tourist Hub') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireScriptConfig
</head>

<body class="bg-slate-50 font-sans text-primary antialiased">
    @php
        $authUser = auth()->user();
        $showBecomeVendor = ! $authUser;
        $touristRegions = $publicTouristRegions ?? collect();
    @endphp
    <div class="flex min-h-screen flex-col" x-data="{ openMenu: false }">
        <header class="sticky top-0 z-40 border-b border-slate-200 bg-white">
            <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/logo/logo.png') }}" alt="GH Tourist Hub logo" class="h-12 w-auto sm:h-14" />
                    {{-- <div>
                        <p class="text-sm font-semibold leading-none text-primary">Tourist Hub</p>
                        <p class="text-xs text-primary/60">Tours and utilities marketplace</p>
                    </div> --}}
                </a>

                <nav class="hidden items-center gap-8 md:flex">
                    <a href="{{ route('home') }}"
                        class="text-base font-semibold {{ request()->routeIs('home') ? 'text-primary' : 'text-primary/70 hover:text-primary' }}">Home</a>
                    <a href="{{ route('marketplace.tours') }}"
                        class="text-base font-semibold {{ request()->routeIs('marketplace.tours') ? 'text-primary' : 'text-primary/70 hover:text-primary' }}">Tours</a>
                    <a href="{{ route('marketplace.utilities') }}"
                        class="text-base font-semibold {{ request()->routeIs('marketplace.utilities') ? 'text-primary' : 'text-primary/70 hover:text-primary' }}">Utilities</a>
                    <div class="relative" x-data="{ openAttractionDesktop: false }">
                        <div class="flex items-center gap-1">
                            <a href="{{ route('marketplace.attractions.index') }}"
                                class="text-base font-semibold {{ request()->routeIs('marketplace.attractions.*') ? 'text-primary' : 'text-primary/70 hover:text-primary' }}">
                                Tourist Attractions
                            </a>
                            @if ($touristRegions->isNotEmpty())
                                <button
                                    type="button"
                                    class="inline-flex h-6 w-6 items-center justify-center rounded text-primary/70 hover:bg-slate-100 hover:text-primary"
                                    @click.stop="openAttractionDesktop = !openAttractionDesktop"
                                    @keydown.escape.window="openAttractionDesktop = false"
                                    aria-label="Toggle tourist attraction regions"
                                >
                                    <svg class="h-4 w-4 transition" :class="{ 'rotate-180': openAttractionDesktop }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                        @if ($touristRegions->isNotEmpty())
                            <div
                                x-cloak
                                x-show="openAttractionDesktop"
                                @click.outside="openAttractionDesktop = false"
                                class="absolute left-0 top-full z-50 mt-3 w-72 rounded-xl border border-slate-200 bg-white p-2 shadow-xl"
                            >
                                <p class="px-2 py-1 text-xs font-semibold uppercase tracking-wider text-primary/50">Regions</p>
                                <div class="max-h-80 overflow-y-auto">
                                    @foreach ($touristRegions as $region)
                                        <a href="{{ route('marketplace.attractions.region', $region->slug) }}" class="block rounded-lg px-2 py-2 text-sm text-primary/80 hover:bg-slate-50 hover:text-primary">
                                            {{ $region->name }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    @if ($showBecomeVendor)
                        <a href="{{ route('register') }}"
                            class="text-base font-semibold text-primary/70 hover:text-primary">Become a Vendor</a>
                    @endif
                </nav>

                <div class="hidden items-center gap-3 md:flex">
                    <div class="me-2">
                        <x-currency-selector />
                    </div>
                    @auth
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}"
                        class="fc-btn fc-btn-outline rounded-full">Dashboard</a>
                    @else
                    <a href="{{ route('login') }}" class="fc-btn fc-btn-outline rounded-full px-5 py-2.5 text-[15px] font-semibold">Log in</a>
                    <a href="{{ route('register') }}" class="fc-btn fc-btn-secondary rounded-full px-5 py-2.5 text-[15px] font-semibold">Get started <span aria-hidden="true">&rarr;</span></a>
                    @endauth
                </div>

                <button @click="openMenu = !openMenu"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 text-primary md:hidden"
                    type="button" aria-label="Toggle menu">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{ 'hidden': openMenu, 'inline-flex': !openMenu }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !openMenu, 'inline-flex': openMenu }" class="hidden"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div x-cloak x-show="openMenu" x-transition class="border-t border-slate-200 bg-white px-4 py-4 md:hidden">
                <div class="space-y-3">
                    <a @click="openMenu=false" href="{{ route('home') }}"
                        class="block text-sm font-medium text-primary">Home</a>
                    <a @click="openMenu=false" href="{{ route('marketplace.tours') }}"
                        class="block text-sm font-medium text-primary">Tours</a>
                    <a @click="openMenu=false" href="{{ route('marketplace.utilities') }}"
                        class="block text-sm font-medium text-primary">Utilities</a>
                    <div x-data="{ openAttractionMobile: false }" class="space-y-2">
                        <button type="button" @click="openAttractionMobile = !openAttractionMobile" class="flex w-full items-center justify-between text-sm font-medium text-primary">
                            <span>Tourist Attractions</span>
                            <svg class="h-4 w-4 transition" :class="{ 'rotate-180': openAttractionMobile }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-cloak x-show="openAttractionMobile" class="ms-3 space-y-2 border-l border-slate-200 ps-3">
                            <a @click="openMenu=false" href="{{ route('marketplace.attractions.index') }}" class="block text-xs font-medium text-primary">All Regions</a>
                            @foreach ($touristRegions as $region)
                                <a @click="openMenu=false" href="{{ route('marketplace.attractions.region', $region->slug) }}"
                                    class="block text-xs font-medium text-primary/80">{{ $region->name }}</a>
                            @endforeach
                        </div>
                    </div>
                    @if ($showBecomeVendor)
                        <a @click="openMenu=false" href="{{ route('register') }}"
                            class="block text-sm font-medium text-primary">Become a Vendor</a>
                    @endif
                    <div class="pt-1">
                        <x-currency-selector />
                    </div>
                    <div class="pt-2">
                        @auth
                        <a @click="openMenu=false" href="{{ route(auth()->user()->dashboardRoute()) }}"
                            class="fc-btn fc-btn-outline w-full">Dashboard</a>
                        @else
                        <a @click="openMenu=false" href="{{ route('login') }}"
                            class="fc-btn fc-btn-outline mb-2 w-full px-5 py-2.5 text-[15px] font-semibold">Log in</a>
                        <a @click="openMenu=false" href="{{ route('register') }}"
                            class="fc-btn fc-btn-primary w-full px-5 py-2.5 text-[15px] font-semibold">Get started <span aria-hidden="true">&rarr;</span></a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            {{ $slot }}
        </main>

        <x-site-footer :show-become-vendor="$showBecomeVendor" />
    </div>
</body>

</html>
