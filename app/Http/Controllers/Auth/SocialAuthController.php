<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SocialAuthController extends Controller
{
    /**
     * Redirect to the configured social auth provider.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->assertSupportedProvider($provider);

        if ($provider === 'linkedin') {
            return Socialite::driver($provider)
                ->scopes(['openid', 'profile', 'email'])
                ->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle callback from social auth provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->assertSupportedProvider($provider);

        $socialUser = Socialite::driver($provider)->user();

        $email = $socialUser->getEmail();

        $user = User::query()
            ->where(function ($query) use ($provider, $socialUser): void {
                $query->where('social_provider', $provider)
                    ->where('social_provider_id', $socialUser->getId());
            })
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->first();

        if (! $user) {
            $fallbackEmail = sprintf('%s@%s.social', $socialUser->getId(), $provider);

            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Traveler '.Str::upper(Str::random(4)),
                'email' => $email ?: $fallbackEmail,
                'password' => Str::password(40),
                'user_role' => User::ROLE_CLIENT,
                'email_verified_at' => now(),
            ]);
        }

        $user->forceFill([
            'social_provider' => $provider,
            'social_provider_id' => $socialUser->getId(),
            'avatar_url' => $socialUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        if ($user->isBlocked()) {
            return redirect()->route('login')->with(
                'status',
                'Your account has been blocked. Contact support@ghtouristhub.com.'
            );
        }

        Auth::login($user, true);

        request()->session()->regenerate();

        return redirect()->intended(route($user->dashboardRoute(), absolute: false));
    }

    /**
     * Ensure only approved providers are used.
     */
    protected function assertSupportedProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'linkedin'], true)) {
            throw new NotFoundHttpException();
        }
    }
}
