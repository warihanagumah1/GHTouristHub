<x-layouts.public>
    <section class="relative overflow-hidden bg-primary text-white">
        <div class="absolute inset-0">
            <div class="absolute -left-12 top-8 h-40 w-40 rounded-br-[3rem] rounded-tl-[3rem] bg-secondary/90"></div>
            <div class="absolute -right-12 bottom-8 h-48 w-48 rounded-tl-[3.5rem] rounded-br-[3.5rem] bg-tertiary/70"></div>
        </div>

        <div class="relative mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
            <x-badge variant="tertiary" class="bg-white/20 text-white">Legal</x-badge>
            <h1 class="mt-4 text-4xl font-bold sm:text-5xl">Privacy Policy</h1>
            <p class="mt-4 max-w-3xl text-white/85">
                This policy explains what information we collect, how we use it, and the choices available to users of {{ config('app.name', 'GH Tourist Hub') }}.
            </p>
            <p class="mt-2 text-sm text-white/75">Last updated: February 28, 2026</p>
        </div>
    </section>

    <section class="mx-auto max-w-5xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="space-y-5">
            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">1. Information we collect</h2>
                <p class="mt-3 text-sm text-primary/80">
                    We collect account details, booking data, listing content, communications, and technical usage data needed to operate,
                    secure, and improve the platform.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">2. How we use information</h2>
                <p class="mt-3 text-sm text-primary/80">
                    We use data to provide marketplace services, process bookings and payouts, support customer service, prevent abuse,
                    and comply with legal obligations.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">3. Sharing and disclosure</h2>
                <p class="mt-3 text-sm text-primary/80">
                    We share information with service providers, payment processors, and vendors as required to complete bookings,
                    operate platform features, and meet legal requirements.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">4. Data retention and security</h2>
                <p class="mt-3 text-sm text-primary/80">
                    We retain data for operational and legal needs and apply administrative and technical safeguards designed
                    to protect personal information.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">5. Your choices</h2>
                <p class="mt-3 text-sm text-primary/80">
                    You may request profile updates, account changes, and support assistance by contacting our support team.
                </p>
            </x-card>

            <x-card class="border-slate-200 bg-white">
                <h2 class="text-xl font-semibold text-primary">6. Contact</h2>
                <p class="mt-3 text-sm text-primary/80">
                    Privacy questions can be sent to
                    <a href="mailto:support@ghtouristhub.com" class="fc-link">support@ghtouristhub.com</a>.
                </p>
            </x-card>
        </div>
    </section>
</x-layouts.public>
