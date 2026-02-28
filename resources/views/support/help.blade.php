<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-primary">Help &amp; Support</h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-4 px-4 sm:px-6 lg:px-8">
            <x-card title="Quick Help">
                <ul class="space-y-2 text-sm text-primary/80">
                    <li>1. Payments: verify booking status from your dashboard before retrying checkout.</li>
                    <li>2. Listings: vendors can manage tours/utilities from the Manage Listings section.</li>
                    <li>3. Payouts: utility/tour owners can review payout totals in vendor dashboard analytics.</li>
                    <li>4. Account: update profile and security settings from Profile Settings.</li>
                </ul>
            </x-card>

            <x-card title="Contact">
                <p class="text-sm text-primary/75">
                    For urgent issues, email <a class="fc-link" href="mailto:support@touristhub.test">support@touristhub.test</a>.
                </p>
            </x-card>
        </div>
    </div>
</x-app-layout>
