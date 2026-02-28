<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route(Auth::user()->dashboardRoute(), absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-primary/75">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form wire:submit="confirmPassword">
        <!-- Password -->
        <div x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input wire:model="password"
                              id="password"
                              class="block w-full pe-20"
                              type="password"
                              x-bind:type="showPassword ? 'text' : 'password'"
                              name="password"
                              required autocomplete="current-password" />
                <button
                    type="button"
                    class="absolute inset-y-0 right-2 my-1 rounded px-2 text-xs font-semibold text-primary/70 hover:bg-slate-100"
                    @click="showPassword = !showPassword"
                >
                    <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</div>
