<?php

namespace App\Livewire\Forms;

use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): bool
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only(['email', 'password']), false)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        $user = Auth::user();

        if ($user && $user->isBlocked()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'form.email' => 'Your account has been blocked. Contact support@ghtouristhub.com.',
            ]);
        }

        if ($user && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'form.email' => 'Please verify your email before logging in.',
            ]);
        }

        if (
            $user
            && config('auth.two_factor.enabled')
            && $user->two_factor_enabled
        ) {
            $code = $user->issueTwoFactorCode();
            $user->notify(new TwoFactorCodeNotification($code));

            Auth::logout();

            session([
                'auth.two_factor.user_id' => $user->id,
                'auth.two_factor.remember' => $this->remember,
            ]);

            return false;
        }

        if ($user && $this->remember) {
            Auth::login($user, true);
        }

        return true;
    }

    /**
     * Ensure the authentication request is not rate limited.
     */
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'form.email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the authentication rate limiting throttle key.
     */
    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->email).'|'.request()->ip());
    }
}
