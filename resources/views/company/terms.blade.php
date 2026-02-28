<x-layouts.public>
    <section class="relative overflow-hidden bg-primary text-white">
        <div class="absolute inset-0">
            <div class="absolute -left-10 bottom-8 h-36 w-36 rounded-tr-[3rem] rounded-bl-[3rem] bg-secondary/90"></div>
            <div class="absolute -right-12 top-8 h-44 w-44 rounded-tl-[3.5rem] rounded-br-[3.5rem] bg-tertiary/70"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-badge variant="tertiary" class="bg-white/20 text-white">Legal</x-badge>
            <h1 class="mt-4 text-4xl font-bold sm:text-5xl">Terms of Service</h1>
            <p class="mt-4 max-w-3xl text-white/85">
                These terms govern use of {{ config('app.name', 'GH Tourist Hub') }} by travelers, vendors, and all account holders.
            </p>
            <p class="mt-2 text-sm text-white/75">Last updated: February 28, 2026</p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="space-y-5">
            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">1. Platform role</h2>
                <p class="mt-3 text-sm text-primary/80">
                    {{ config('app.name', 'GH Tourist Hub') }} provides a marketplace that connects travelers with third-party vendors offering tours and utility services.
                    Vendors are responsible for the accuracy of listing content and fulfillment of booked services.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">2. Accounts and eligibility</h2>
                <p class="mt-3 text-sm text-primary/80">
                    You must provide accurate information when creating an account. You are responsible for maintaining account security
                    and for all activity that occurs under your credentials.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">3. Bookings, pricing, and payments</h2>
                <p class="mt-3 text-sm text-primary/80">
                    Listing prices, availability, and policies are set by vendors. Payments may be processed through integrated payment providers.
                    Refunds and cancellations are handled according to each listing policy and applicable law.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">4. Reviews and content</h2>
                <p class="mt-3 text-sm text-primary/80">
                    Reviews should reflect genuine booking experiences. We may moderate or remove content that is fraudulent, abusive,
                    defamatory, misleading, or otherwise violates platform rules.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">5. Suspension and termination</h2>
                <p class="mt-3 text-sm text-primary/80">
                    We may suspend or terminate accounts that violate these terms, threaten platform safety, or misuse bookings, payouts, or reviews.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">6. Contact</h2>
                <p class="mt-3 text-sm text-primary/80">
                    Questions regarding these Terms can be sent to
                    <a href="mailto:support@ghtouristhub.com" class="fc-link">support@ghtouristhub.com</a>.
                </p>
            </x-card>
        </div>
    </section>
</x-layouts.public>
