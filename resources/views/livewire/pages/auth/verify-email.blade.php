<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route(Auth::user()->dashboardRoute(), absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Check latest verification status and continue.
     */
    public function alreadyVerified(): void
    {
        $user = Auth::user()?->fresh();

        if ($user && $user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route($user->dashboardRoute(), absolute: false), navigate: true);

            return;
        }

        Session::flash('status', 'verification-still-pending');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-primary/75">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif
    @if (session('status') == 'verification-still-pending')
        <x-alert variant="warning" class="mb-4">
            Your email is not verified yet. Open your verification link, then click "Already Verified".
        </x-alert>
    @endif

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
        <x-primary-button wire:click="sendVerification">
            {{ __('Resend Verification Email') }}
        </x-primary-button>

        <x-button variant="outline" wire:click="alreadyVerified">
            {{ __('Already Verified') }}
        </x-button>

        <button wire:click="logout" type="submit" class="fc-link">
            {{ __('Log Out') }}
        </button>
    </div>
</div>
