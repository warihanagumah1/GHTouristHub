<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $authenticated = $this->form->authenticate();

        if (! $authenticated) {
            $this->redirectRoute('two-factor.challenge', navigate: true);

            return;
        }

        Session::regenerate();

        $this->redirectIntended(default: route(auth()->user()->dashboardRoute(), absolute: false), navigate: true);
    }
}; ?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-5 space-y-3">
        <x-social-auth-button
            provider="google"
            :href="route('social.redirect', ['provider' => 'google'])"
            label="Continue with Google"
        />
        <x-social-auth-button
            provider="linkedin"
            :href="route('social.redirect', ['provider' => 'linkedin'])"
            label="Continue with LinkedIn"
        />
    </div>

    <div class="mb-5 flex items-center gap-3 text-xs uppercase tracking-wider text-primary/50">
        <span class="h-px flex-1 bg-slate-200"></span>
        or
        <span class="h-px flex-1 bg-slate-200"></span>
    </div>

    <form wire:submit.prevent="login">
        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="form.email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('form.email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input
                    x-ref="passwordInput"
                    wire:model="form.password"
                    id="password"
                    class="block w-full pe-20"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-2 my-1 rounded px-2 text-xs font-semibold text-primary/70 hover:bg-slate-100"
                    @click="
                        showPassword = !showPassword;
                        $refs.passwordInput.type = showPassword ? 'text' : 'password';
                    "
                >
                    <span x-text="showPassword ? 'Hide' : 'Show'"></span>
                </button>
            </div>

            <x-input-error :messages="$errors->get('form.password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember" class="inline-flex items-center">
                <x-checkbox-input wire:model="form.remember" id="remember" name="remember" />
                <span class="ms-2 text-sm text-primary/75">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="fc-link" href="{{ route('password.request') }}" wire:navigate>
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="mt-4 text-sm text-primary/75">
                New user?
                <a class="fc-link" href="{{ route('register') }}" wire:navigate>Register</a>
            </p>
        @endif
    </form>
</div>
