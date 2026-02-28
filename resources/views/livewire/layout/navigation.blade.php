<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

@php
    $authUser = auth()->user();
    $isVendorUser = $authUser->isVendor();
    $isAdminUser = in_array($authUser->user_role, [\App\Models\User::ROLE_ADMIN, \App\Models\User::ROLE_ADMIN_STAFF], true);
    $initial = strtoupper(substr((string) $authUser->name, 0, 1));
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-secondary/20">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-20 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" wire:navigate>
                        <x-application-logo class="block h-16 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')" wire:navigate>
                        {{ __('Home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('marketplace.tours')" :active="request()->routeIs('marketplace.tours')" wire:navigate>
                        {{ __('Tours') }}
                    </x-nav-link>
                    <x-nav-link :href="route('marketplace.utilities')" :active="request()->routeIs('marketplace.utilities')" wire:navigate>
                        {{ __('Utilities') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-4">
                <x-currency-selector />
                <x-dropdown align="right" width="w-72" contentClasses="bg-white py-0">
                    <x-slot name="trigger">
                        <button class="inline-flex w-72 items-center gap-2 rounded-full border-[3px] border-tertiary bg-white px-2.5 py-1.5 text-left shadow-sm transition hover:border-primary focus:outline-none">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-tertiary text-base font-semibold text-white">
                                {{ $initial }}
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold leading-tight text-primary">{{ $authUser->name }}</span>
                                <span class="block truncate text-xs leading-tight text-primary/65">{{ $authUser->email }}</span>
                            </span>
                            <svg class="h-5 w-5 text-primary/55" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="overflow-hidden rounded-2xl border border-slate-200">
                            <div class="p-2">
                                <a href="{{ route($authUser->dashboardRoute()) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                    <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1V3zM3 12a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM11 3a1 1 0 011-1h4a1 1 0 011 1v14a1 1 0 01-1 1h-4a1 1 0 01-1-1V3z"/></svg>
                                    <span>Dashboard</span>
                                </a>

                                <a href="{{ route('profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                    <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17a1 1 0 00-1.98 0l-.14.91a1 1 0 01-1.18.82l-.9-.2a1 1 0 00-1.16 1.16l.2.9a1 1 0 01-.82 1.18l-.91.14a1 1 0 000 1.98l.91.14a1 1 0 01.82 1.18l-.2.9a1 1 0 001.16 1.16l.9-.2a1 1 0 011.18.82l.14.91a1 1 0 001.98 0l.14-.91a1 1 0 011.18-.82l.9.2a1 1 0 001.16-1.16l-.2-.9a1 1 0 01.82-1.18l.91-.14a1 1 0 000-1.98l-.91-.14a1 1 0 01-.82-1.18l.2-.9a1 1 0 00-1.16-1.16l-.9.2a1 1 0 01-1.18-.82l-.14-.91zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                                    <span>Profile Settings</span>
                                </a>

                                @if ($isAdminUser)
                                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1V3zM3 12a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM11 3a1 1 0 011-1h4a1 1 0 011 1v14a1 1 0 01-1 1h-4a1 1 0 01-1-1V3z"/></svg>
                                        <span>Admin Dashboard</span>
                                    </a>
                                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 10a4 4 0 100-8 4 4 0 000 8zM2 18a8 8 0 1116 0H2z"/></svg>
                                        <span>Users</span>
                                    </a>
                                    <a href="{{ route('admin.listings.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0017.414 6l-3.414-3.414A2 2 0 0012.586 2H4zm7 1.5V7a1 1 0 001 1h2.5L11 4.5z"/></svg>
                                        <span>Listings</span>
                                    </a>
                                    <a href="{{ route('admin.analytics') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5H2v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9H8V7zM14 3a1 1 0 011-1h2a1 1 0 011 1v13h-4V3z"/></svg>
                                        <span>Analytics</span>
                                    </a>
                                    <a href="{{ route('admin.payouts.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2H2V5zm0 4h16v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 2a1 1 0 100 2h2a1 1 0 100-2H6z"/></svg>
                                        <span>Payouts</span>
                                    </a>
                                    <a href="{{ route('admin.support-tickets.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zM8 8a2 2 0 114 0c0 1.5-2 1.25-2 3h-1a3 3 0 013-3 1 1 0 10-1 1H8zM9 14h2v2H9v-2z" clip-rule="evenodd"/></svg>
                                        <span>Support Tickets</span>
                                    </a>
                                @elseif ($isVendorUser)
                                    <a href="{{ route('vendor.listings.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0017.414 6l-3.414-3.414A2 2 0 0012.586 2H4zm7 1.5V7a1 1 0 001 1h2.5L11 4.5z"/></svg>
                                        <span>Manage Listings</span>
                                    </a>
                                    <a href="{{ route('vendor.bookings.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 2v8h12V7H4zm2 2h3v2H6V9z"/></svg>
                                        <span>Bookings</span>
                                    </a>
                                    <a href="{{ route('vendor.reviews.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"/></svg>
                                        <span>Reviews</span>
                                    </a>
                                    <a href="{{ route('vendor.payouts.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2H2V5zm0 4h16v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 2a1 1 0 100 2h2a1 1 0 100-2H6z"/></svg>
                                        <span>Payouts</span>
                                    </a>
                                    <a href="{{ route('vendor.site-profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h5.586a2 2 0 001.414-.586l4.414-4.414A2 2 0 0016 10.586V5a2 2 0 00-2-2H4z"/></svg>
                                        <span>Mini Website</span>
                                    </a>
                                    @if (in_array($authUser->user_role, [\App\Models\User::ROLE_TOUR_OWNER, \App\Models\User::ROLE_UTILITY_OWNER], true))
                                        <a href="{{ route('vendor.team.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                            <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M7 9a3 3 0 100-6 3 3 0 000 6zM13 9a3 3 0 100-6 3 3 0 000 6zM2 16a5 5 0 0110 0H2zM8 16a5 5 0 0110 0H8z"/></svg>
                                            <span>Team</span>
                                        </a>
                                    @endif
                                @endif

                                @if (! $isAdminUser)
                                    <a href="{{ route('support.tickets') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 3a3 3 0 00-3 3v2.5a2.5 2.5 0 001 2v2A2.5 2.5 0 005.5 15H6v1a1 1 0 001.447.894L10 15.618l2.553 1.276A1 1 0 0014 16v-1h.5A2.5 2.5 0 0017 12.5v-2a2.5 2.5 0 001-2V6a3 3 0 00-3-3H5z" clip-rule="evenodd"/></svg>
                                        <span>Support Tickets</span>
                                    </a>
                                    <a href="{{ route('support.help') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-base text-primary/90 transition hover:bg-slate-50" wire:navigate>
                                        <svg class="h-5 w-5 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8 4a1 1 0 100 2 1 1 0 000-2zm-1-2a1 1 0 102 0V8a1 1 0 10-2 0v4z" clip-rule="evenodd"/></svg>
                                        <span>Help &amp; Support</span>
                                    </a>
                                @endif
                            </div>

                            <div class="border-t border-slate-200 p-2">
                                <button wire:click="logout" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-base font-medium text-secondary transition hover:bg-secondary/10">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25V6a1 1 0 11-2 0V4.25a.25.25 0 00-.25-.25h-5.5a.25.25 0 00-.25.25v11.5c0 .138.112.25.25.25h5.5a.25.25 0 00.25-.25V14a1 1 0 112 0v1.75A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25zm9.22 2.97a.75.75 0 011.06 0l2.25 2.25a.75.75 0 010 1.06l-2.25 2.25a.75.75 0 01-1.06-1.06l.97-.97H8a.75.75 0 010-1.5h5.19l-.97-.97a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                                    <span>Sign Out</span>
                                </button>
                            </div>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="border-b border-secondary/20 px-4 py-3">
            <x-currency-selector />
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route(auth()->user()->dashboardRoute())" :active="request()->routeIs(auth()->user()->dashboardRoute())" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M3 3a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1V3zM3 12a1 1 0 011-1h5a1 1 0 011 1v5a1 1 0 01-1 1H4a1 1 0 01-1-1v-5zM11 3a1 1 0 011-1h4a1 1 0 011 1v14a1 1 0 01-1 1h-4a1 1 0 01-1-1V3z"/></svg>
                    <span>{{ __('Dashboard') }}</span>
                </span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('marketplace.tours')" :active="request()->routeIs('marketplace.tours')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10.5 3.75a.75.75 0 00-1.5 0V10H3.75a.75.75 0 000 1.5H9v5.25a.75.75 0 001.5 0V11.5h5.25a.75.75 0 000-1.5H10.5V3.75z"/></svg>
                    <span>{{ __('Tours') }}</span>
                </span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('marketplace.utilities')" :active="request()->routeIs('marketplace.utilities')" wire:navigate>
                <span class="inline-flex items-center gap-2">
                    <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 4.5A1.5 1.5 0 013.5 3h13A1.5 1.5 0 0118 4.5v11A1.5 1.5 0 0116.5 17h-13A1.5 1.5 0 012 15.5v-11zM4 6v8h12V6H4z"/></svg>
                    <span>{{ __('Utilities') }}</span>
                </span>
            </x-responsive-nav-link>
            @if (auth()->user()->user_role === \App\Models\User::ROLE_CLIENT)
                <x-responsive-nav-link :href="route('client.bookings.index')" :active="request()->routeIs('client.bookings.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 2v8h12V7H4zm2 2h3v2H6V9z"/></svg>
                        <span>{{ __('Bookings') }}</span>
                    </span>
                </x-responsive-nav-link>
            @endif
            @if (auth()->user()->isVendor())
                <x-responsive-nav-link :href="route('vendor.listings.index')" :active="request()->routeIs('vendor.listings.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0017.414 6l-3.414-3.414A2 2 0 0012.586 2H4zm7 1.5V7a1 1 0 001 1h2.5L11 4.5z"/></svg>
                        <span>{{ __('Listings') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.payouts.index')" :active="request()->routeIs('vendor.payouts.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2H2V5zm0 4h16v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 2a1 1 0 100 2h2a1 1 0 100-2H6z"/></svg>
                        <span>{{ __('Payouts') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.bookings.index')" :active="request()->routeIs('vendor.bookings.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm2 2v8h12V7H4zm2 2h3v2H6V9z"/></svg>
                        <span>{{ __('Bookings') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.reviews.index')" :active="request()->routeIs('vendor.reviews.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"/></svg>
                        <span>{{ __('Reviews') }}</span>
                    </span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('vendor.site-profile.edit')" :active="request()->routeIs('vendor.site-profile.*')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h5.586a2 2 0 001.414-.586l4.414-4.414A2 2 0 0016 10.586V5a2 2 0 00-2-2H4z"/></svg>
                        <span>{{ __('Mini Website') }}</span>
                    </span>
                </x-responsive-nav-link>
                @if (in_array(auth()->user()->user_role, [\App\Models\User::ROLE_TOUR_OWNER, \App\Models\User::ROLE_UTILITY_OWNER], true))
                    <x-responsive-nav-link :href="route('vendor.team.index')" :active="request()->routeIs('vendor.team.*')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M7 9a3 3 0 100-6 3 3 0 000 6zM13 9a3 3 0 100-6 3 3 0 000 6zM2 16a5 5 0 0110 0H2zM8 16a5 5 0 0110 0H8z"/></svg>
                            <span>{{ __('Team') }}</span>
                        </span>
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.49 3.17a1 1 0 00-1.98 0l-.14.91a1 1 0 01-1.18.82l-.9-.2a1 1 0 00-1.16 1.16l.2.9a1 1 0 01-.82 1.18l-.91.14a1 1 0 000 1.98l.91.14a1 1 0 01.82 1.18l-.2.9a1 1 0 001.16 1.16l.9-.2a1 1 0 011.18.82l.14.91a1 1 0 001.98 0l.14-.91a1 1 0 011.18-.82l.9.2a1 1 0 001.16-1.16l-.2-.9a1 1 0 01.82-1.18l.91-.14a1 1 0 000-1.98l-.91-.14a1 1 0 01-.82-1.18l.2-.9a1 1 0 00-1.16-1.16l-.9.2a1 1 0 01-1.18-.82l-.14-.91zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                        <span>{{ __('Profile Settings') }}</span>
                    </span>
                </x-responsive-nav-link>

                @if ($isAdminUser)
                    <x-responsive-nav-link :href="route('admin.users.index')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M10 10a4 4 0 100-8 4 4 0 000 8zM2 18a8 8 0 1116 0H2z"/></svg>
                            <span>{{ __('Users') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.listings.index')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V7.414A2 2 0 0017.414 6l-3.414-3.414A2 2 0 0012.586 2H4zm7 1.5V7a1 1 0 001 1h2.5L11 4.5z"/></svg>
                            <span>{{ __('Listings') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.analytics')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5H2v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9H8V7zM14 3a1 1 0 011-1h2a1 1 0 011 1v13h-4V3z"/></svg>
                            <span>{{ __('Analytics') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.payouts.index')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 5a2 2 0 012-2h12a2 2 0 012 2v2H2V5zm0 4h16v6a2 2 0 01-2 2H4a2 2 0 01-2-2V9zm4 2a1 1 0 100 2h2a1 1 0 100-2H6z"/></svg>
                            <span>{{ __('Payouts') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.support-tickets.index')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zM8 8a2 2 0 114 0c0 1.5-2 1.25-2 3h-1a3 3 0 013-3 1 1 0 10-1 1H8zM9 14h2v2H9v-2z" clip-rule="evenodd"/></svg>
                            <span>{{ __('Support Tickets') }}</span>
                        </span>
                    </x-responsive-nav-link>
                @endif

                @if (! $isAdminUser)
                    <x-responsive-nav-link :href="route('support.tickets')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5 3a3 3 0 00-3 3v2.5a2.5 2.5 0 001 2v2A2.5 2.5 0 005.5 15H6v1a1 1 0 001.447.894L10 15.618l2.553 1.276A1 1 0 0014 16v-1h.5A2.5 2.5 0 0017 12.5v-2a2.5 2.5 0 001-2V6a3 3 0 00-3-3H5z" clip-rule="evenodd"/></svg>
                            <span>{{ __('Support Tickets') }}</span>
                        </span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('support.help')" wire:navigate>
                        <span class="inline-flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0 text-secondary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 112 10a8 8 0 0116 0zm-8 4a1 1 0 100 2 1 1 0 000-2zm-1-2a1 1 0 102 0V8a1 1 0 10-2 0v4z" clip-rule="evenodd"/></svg>
                            <span>{{ __('Help & Support') }}</span>
                        </span>
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start rounded-md px-4 py-2 text-sm font-medium text-secondary hover:bg-secondary/10">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25V6a1 1 0 11-2 0V4.25a.25.25 0 00-.25-.25h-5.5a.25.25 0 00-.25.25v11.5c0 .138.112.25.25.25h5.5a.25.25 0 00.25-.25V14a1 1 0 112 0v1.75A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25zm9.22 2.97a.75.75 0 011.06 0l2.25 2.25a.75.75 0 010 1.06l-2.25 2.25a.75.75 0 01-1.06-1.06l.97-.97H8a.75.75 0 010-1.5h5.19l-.97-.97a.75.75 0 010-1.06z" clip-rule="evenodd"/></svg>
                        <span>{{ __('Sign Out') }}</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</nav>
