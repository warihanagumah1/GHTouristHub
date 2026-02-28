<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $enabled = false;

    public function mount(): void
    {
        $this->enabled = (bool) Auth::user()->two_factor_enabled;
    }

    /**
     * Turn on optional email-based 2FA.
     */
    public function enableTwoFactor(): void
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_enabled' => true,
        ])->save();

        $this->enabled = true;
        $this->dispatch('two-factor-updated');
    }

    /**
     * Turn off optional email-based 2FA.
     */
    public function disableTwoFactor(): void
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();

        $this->enabled = false;
        $this->dispatch('two-factor-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-primary">
            {{ __('Two-factor authentication') }}
        </h2>

        <p class="mt-1 text-sm text-primary/75">
            {{ __('Add an extra sign-in step by requiring a one-time code delivered to your email.') }}
        </p>
    </header>

    <div class="mt-6 space-y-4">
        <x-alert :variant="$enabled ? 'success' : 'info'">
            {{ $enabled ? __('Two-factor authentication is currently enabled.') : __('Two-factor authentication is currently disabled.') }}
        </x-alert>

        <div class="flex items-center gap-4">
            @if ($enabled)
                <x-secondary-button wire:click="disableTwoFactor">
                    {{ __('Disable 2FA') }}
                </x-secondary-button>
            @else
                <x-primary-button wire:click="enableTwoFactor">
                    {{ __('Enable 2FA') }}
                </x-primary-button>
            @endif

            <x-action-message on="two-factor-updated">
                {{ __('Security setting updated.') }}
            </x-action-message>
        </div>
    </div>
</section>
