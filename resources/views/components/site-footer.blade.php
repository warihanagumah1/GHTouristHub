@props([
    'showBecomeVendor' => null,
])

@php
    $authUser = auth()->user();
    $showBecomeVendor = $showBecomeVendor ?? ! $authUser;
@endphp

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div>
            <img src="{{ asset('images/logo/logo.png') }}" alt="GH Tourist Hub logo" class="h-10 w-auto" />
            <p class="mt-2 text-sm text-primary/70">
                Multi-tenant marketplace for tours, utilities, and unforgettable travel services.
            </p>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary/70">Marketplace</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('home') }}" class="text-primary/75 hover:text-primary">Home</a></li>
                <li><a href="{{ route('marketplace.tours') }}" class="text-primary/75 hover:text-primary">Tours</a></li>
                <li><a href="{{ route('marketplace.utilities') }}" class="text-primary/75 hover:text-primary">Utilities</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary/70">Company</p>
            <ul class="mt-3 space-y-2 text-sm">
                <li><a href="{{ route('company.about') }}" class="text-primary/75 hover:text-primary">About</a></li>
                <li><a href="{{ route('company.terms') }}" class="text-primary/75 hover:text-primary">Terms</a></li>
                <li><a href="{{ route('company.privacy') }}" class="text-primary/75 hover:text-primary">Privacy</a></li>
            </ul>
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-primary/70">Contact</p>
            <a href="mailto:support@ghtouristhub.com" class="mt-3 block text-sm text-primary/75 hover:text-primary">support@ghtouristhub.com</a>
            <p class="mt-1 text-sm text-primary/75">+233 20 000 0000</p>
            @if ($showBecomeVendor)
                <a href="{{ route('register') }}" class="fc-btn fc-btn-secondary mt-4 w-full">Become a Vendor</a>
            @else
                <a href="{{ route($authUser->dashboardRoute()) }}" class="fc-btn fc-btn-outline mt-4 w-full">Go to Dashboard</a>
            @endif
        </div>
    </div>
    <div class="border-t border-slate-100 bg-primary py-4">
        <p class="text-center text-xs text-white/75">
            © {{ now()->year }} {{ config('app.name', 'GH Tourist Hub') }}. All rights reserved.
        </p>
    </div>
</footer>
