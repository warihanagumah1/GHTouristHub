<?php

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    public function mount(): void
    {
        if (! session()->has('auth.two_factor.user_id')) {
            $this->redirectRoute('login', navigate: true);
        }
    }

    /**
     * Verify the provided 2FA code and complete sign-in.
     */
    public function verifyCode(): void
    {
        $this->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            $seconds = RateLimiter::availableIn($this->throttleKey());

            throw ValidationException::withMessages([
                'code' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $userId = (int) session('auth.two_factor.user_id');
        $remember = (bool) session('auth.two_factor.remember', false);

        $user = User::find($userId);

        if (! $user || $user->isBlocked() || ! $user->two_factor_enabled || ! $user->hasValidTwoFactorCode($this->code)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'code' => __('Invalid or expired verification code.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
        $user->clearTwoFactorCode();

        session()->forget([
            'auth.two_factor.user_id',
            'auth.two_factor.remember',
        ]);

        Auth::login($user, $remember);
        Session::regenerate();

        $this->redirectIntended(default: route($user->dashboardRoute(), absolute: false), navigate: true);
    }

    /**
     * Resend a fresh 2FA code to the user.
     */
    public function resendCode(): void
    {
        $userId = (int) session('auth.two_factor.user_id');
        $user = User::find($userId);

        if (! $user || $user->isBlocked() || ! $user->two_factor_enabled) {
            $this->redirectRoute('login', navigate: true);

            return;
        }

        $limitKey = '2fa-resend:'.$userId.'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($limitKey, 3)) {
            $seconds = RateLimiter::availableIn($limitKey);

            throw ValidationException::withMessages([
                'code' => "Please wait {$seconds} seconds before requesting another code.",
            ]);
        }

        RateLimiter::hit($limitKey, 60);

        $code = $user->issueTwoFactorCode();
        $user->notify(new TwoFactorCodeNotification($code));

        session()->flash('status', 'verification-code-resent');
    }

    protected function throttleKey(): string
    {
        return '2fa-login:'.session('auth.two_factor.user_id').'|'.request()->ip();
    }
}; ?>

<div>
    <h1 class="mb-2 text-xl font-semibold text-primary">Two-factor verification</h1>
    <p class="mb-4 text-sm text-primary/75">
        Enter the 6-digit verification code we sent to your email to finish signing in.
    </p>

    @if (session('status') === 'verification-code-resent')
        <x-alert variant="success" class="mb-4">
            A new verification code was sent to your email address.
        </x-alert>
    @endif

    <form wire:submit="verifyCode" class="space-y-4">
        <div>
            <x-input-label for="code" value="Verification code" />
            <x-text-input wire:model="code" id="code" class="mt-1 block w-full" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required autofocus />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between">
            <x-primary-button>
                Verify and continue
            </x-primary-button>

            <button type="button" wire:click="resendCode" class="fc-link">
                Resend code
            </button>
        </div>
    </form>
</div>
