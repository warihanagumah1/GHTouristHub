<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-primary leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-primary">
                    {{ __("You're logged in!") }}
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('client.dashboard') }}" class="fc-btn fc-btn-outline">Client Dashboard</a>
                        <a href="{{ route('vendor.dashboard') }}" class="fc-btn fc-btn-outline">Vendor Dashboard</a>
                        <a href="{{ route('admin.dashboard') }}" class="fc-btn fc-btn-outline">Admin Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
