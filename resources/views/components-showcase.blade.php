<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-primary">
            {{ __('UI Components') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-card title="Buttons" subtitle="Primary, secondary, tertiary, outline and ghost variants">
                <div class="flex flex-wrap gap-3">
                    <x-primary-button type="button">Primary</x-primary-button>
                    <x-secondary-button type="button">Secondary</x-secondary-button>
                    <x-danger-button type="button">Danger</x-danger-button>
                    <x-tertiary-button type="button">Tertiary</x-tertiary-button>
                    <x-outline-button type="button">Outline</x-outline-button>
                    <x-ghost-button type="button">Ghost</x-ghost-button>
                </div>
            </x-card>

            <x-card title="Form Elements" subtitle="Inputs styled with primary/secondary/tertiary palette">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="showcase_name" value="Name" />
                        <x-text-input id="showcase_name" class="mt-1" type="text" placeholder="Jane Doe" />
                    </div>
                    <div>
                        <x-input-label for="showcase_country" value="Country" />
                        <x-select-input id="showcase_country" class="mt-1">
                            <option>Ghana</option>
                            <option>Kenya</option>
                            <option>Tanzania</option>
                        </x-select-input>
                    </div>
                    <div class="md:col-span-2">
                        <x-input-label for="showcase_message" value="Message" />
                        <x-textarea-input id="showcase_message" class="mt-1" placeholder="Share your travel plans..."></x-textarea-input>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-checkbox-input id="showcase_terms" />
                        <label for="showcase_terms" class="text-sm text-primary/75">I agree to the terms</label>
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                            <x-radio-input name="trip_type" checked />
                            Standard
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-primary/75">
                            <x-radio-input name="trip_type" />
                            Premium
                        </label>
                    </div>
                </div>
            </x-card>

            <div class="grid gap-6 lg:grid-cols-2">
                <x-card title="Badges">
                    <div class="flex flex-wrap gap-2">
                        <x-badge variant="primary">Primary</x-badge>
                        <x-badge variant="secondary">Secondary</x-badge>
                        <x-badge variant="tertiary">Tertiary</x-badge>
                    </div>
                </x-card>

                <x-card title="Alerts">
                    <div class="space-y-3">
                        <x-alert variant="info">New route recommendations are available.</x-alert>
                        <x-alert variant="success">Your profile has been updated.</x-alert>
                        <x-alert variant="warning">Your passport expires in 3 months.</x-alert>
                        <x-alert variant="danger">Payment authorization failed.</x-alert>
                    </div>
                </x-card>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <x-stat-card label="Trips Booked" value="124" trend="+12% this month" trend-direction="up" />
                <x-stat-card label="Open Requests" value="18" trend="2 overdue" trend-direction="down" />
                <x-stat-card label="Avg. Rating" value="4.8 / 5" trend="Stable week over week" />
            </div>
        </div>
    </div>
</x-app-layout>
