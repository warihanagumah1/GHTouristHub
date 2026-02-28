<x-layouts.public>
    <section class="relative overflow-hidden bg-primary text-white">
        <div class="absolute inset-0">
            <div class="absolute -left-12 top-10 h-40 w-40 rounded-br-[3rem] rounded-tl-[3rem] bg-secondary/90"></div>
            <div class="absolute -right-14 bottom-0 h-52 w-52 rounded-tl-[4rem] rounded-br-[4rem] bg-tertiary/70"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <x-badge variant="tertiary" class="bg-white/20 text-white">About {{ config('app.name', 'GH Tourist Hub') }}</x-badge>
            <h1 class="mt-5 max-w-3xl text-4xl font-bold leading-tight sm:text-5xl">
                Built to connect travelers with trusted tour and utility companies in one place.
            </h1>
            <p class="mt-5 max-w-2xl text-lg text-white/85">
                {{ config('app.name', 'GH Tourist Hub') }} is a multi-tenant marketplace where verified operators list tours, transport, hotels, attractions, and event services across top destinations.
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button-link :href="route('marketplace.tours')" variant="secondary">Explore Tours</x-button-link>
                <x-button-link :href="route('marketplace.utilities')" variant="outline" class="text-primary hover:bg-white/90">Browse Utilities</x-button-link>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-5 md:grid-cols-3">
            <x-card class="border-secondary/20 bg-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Verified Supply</p>
                <h2 class="mt-2 text-xl font-semibold text-primary">Trusted operators only</h2>
                <p class="mt-3 text-sm text-primary/75">
                    We focus on approved tour and utility companies so travelers can book with confidence and clear expectations.
                </p>
            </x-card>
            <x-card class="border-secondary/20 bg-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Simple Booking</p>
                <h2 class="mt-2 text-xl font-semibold text-primary">Fast, clear checkout</h2>
                <p class="mt-3 text-sm text-primary/75">
                    Discover listings, compare details, and complete bookings through one streamlined marketplace experience.
                </p>
            </x-card>
            <x-card class="border-secondary/20 bg-white">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Vendor Growth</p>
                <h2 class="mt-2 text-xl font-semibold text-primary">Tools for operators</h2>
                <p class="mt-3 text-sm text-primary/75">
                    Vendors manage listings, bookings, payouts, reviews, and mini websites from one dashboard.
                </p>
            </x-card>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
            <div>
                <h2 class="text-3xl font-bold text-primary">Our mission</h2>
                <p class="mt-4 text-primary/75">
                    Make tourism services easier to access and easier to trust by giving travelers transparent choices and giving operators the right digital tools.
                </p>
                <p class="mt-4 text-primary/75">
                    We are building a marketplace where great local businesses can be discovered globally, with quality, speed, and reliability at the center.
                </p>
            </div>
            <x-card class="border-tertiary/40 bg-gradient-to-br from-white via-white to-tertiary/10">
                <h3 class="text-xl font-semibold text-primary">Why travelers and vendors choose us</h3>
                <ul class="mt-4 space-y-3 text-sm text-primary/80">
                    <li>1. One marketplace for tours and utilities.</li>
                    <li>2. Rich listing content with galleries, policies, and itineraries.</li>
                    <li>3. Ratings and reviews for better booking decisions.</li>
                    <li>4. Vendor dashboards with booking and payout workflows.</li>
                </ul>
            </x-card>
        </div>
    </section>
</x-layouts.public>
