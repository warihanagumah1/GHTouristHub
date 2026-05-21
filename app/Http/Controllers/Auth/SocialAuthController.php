<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\User;
use App\Models\VendorProfile;
use App\Notifications\VendorRegistrationPendingApprovalNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\Provider;
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
        $flow = $this->normalizeFlow((string) request()->query('flow', 'login'));

        $requestedAccountType = request()->query('account_type');

        request()->session()->put(
            'social_auth.flow',
            $flow
        );

        request()->session()->put(
            'social_auth.account_type',
            $flow === 'register' && is_string($requestedAccountType)
                ? $this->normalizeAccountType($requestedAccountType)
                : null
        );

        $driver = $this->socialDriver($provider);

        if ($provider === 'linkedin') {
            return $driver
                ->scopes(['openid', 'profile', 'email'])
                ->redirect();
        }

        return $driver->redirect();
    }

    /**
     * Handle callback from social auth provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->assertSupportedProvider($provider);

        $flow = $this->normalizeFlow((string) request()->session()->pull('social_auth.flow', 'login'));
        $selectedAccountType = request()->session()->pull('social_auth.account_type');
        $selectedAccountType = is_string($selectedAccountType) ? $this->normalizeAccountType($selectedAccountType) : null;
        $socialUser = $this->socialDriver($provider)->user();

        $email = $socialUser->getEmail();
        $isNewUser = false;

        $user = User::query()
            ->where(function ($query) use ($provider, $socialUser): void {
                $query->where('social_provider', $provider)
                    ->where('social_provider_id', $socialUser->getId());
            })
            ->when($email, fn ($query) => $query->orWhere('email', $email))
            ->first();

        if (! $user) {
            if ($flow === 'register' && $selectedAccountType === null) {
                return redirect()->route('register')->with(
                    'status',
                    'Select an account type before continuing with social signup.'
                );
            }

            $fallbackEmail = sprintf('%s@%s.social', $socialUser->getId(), $provider);
            $userRole = $selectedAccountType !== null
                ? $this->roleForAccountType($selectedAccountType)
                : User::ROLE_CLIENT;

            $user = User::create([
                'name' => $socialUser->getName() ?: $socialUser->getNickname() ?: 'Traveler '.Str::upper(Str::random(4)),
                'email' => $email ?: $fallbackEmail,
                'password' => Str::password(40),
                'user_role' => $userRole,
                'email_verified_at' => now(),
            ]);
            $isNewUser = true;
        }

        if ($isNewUser) {
            $this->provisionVendorAccount($user);
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

    /**
     * Build provider driver using current host/scheme callback URL.
     */
    protected function socialDriver(string $provider): Provider
    {
        return Socialite::driver($provider)->redirectUrl($this->callbackUrl($provider));
    }

    /**
     * Resolve callback URL, preferring provider config and falling back to current host/port.
     */
    protected function callbackUrl(string $provider): string
    {
        $configuredRedirect = (string) config("services.{$provider}.redirect");

        if ($configuredRedirect !== '') {
            return $configuredRedirect;
        }

        return route('social.callback', ['provider' => $provider], true);
    }

    protected function normalizeAccountType(string $accountType): ?string
    {
        return in_array($accountType, [User::ROLE_CLIENT, User::ROLE_TOUR_OWNER, User::ROLE_UTILITY_OWNER], true)
            ? $accountType
            : null;
    }

    protected function normalizeFlow(string $flow): string
    {
        return in_array($flow, ['login', 'register'], true) ? $flow : 'login';
    }

    protected function roleForAccountType(string $accountType): string
    {
        return match ($accountType) {
            User::ROLE_TOUR_OWNER => User::ROLE_TOUR_OWNER,
            User::ROLE_UTILITY_OWNER => User::ROLE_UTILITY_OWNER,
            default => User::ROLE_CLIENT,
        };
    }

    protected function provisionVendorAccount(User $user): void
    {
        if (! $user->isVendor() || $user->primaryTenant()) {
            return;
        }

        $tenantType = $user->user_role === User::ROLE_TOUR_OWNER ? 'tour_company' : 'utility_owner';
        $tenant = Tenant::create([
            'name' => "{$user->name} ".__('Business'),
            'slug' => Str::slug($user->name.'-'.Str::lower(Str::random(5))),
            'type' => $tenantType,
            'status' => 'pending',
            'owner_user_id' => $user->id,
        ]);

        TenantMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => $user->user_role,
            'permissions' => [
                'listings.manage' => true,
                'bookings.manage' => true,
                'profile.manage' => true,
            ],
            'is_active' => true,
        ]);

        VendorProfile::create([
            'tenant_id' => $tenant->id,
            'support_email' => $user->email,
            'kyc_status' => 'draft',
        ]);

        Notification::route(
            'mail',
            (string) config('services.support.admin_email', 'support@ghtouristhub.com')
        )->notify(new VendorRegistrationPendingApprovalNotification($user, $tenant));

        User::query()
            ->whereIn('user_role', [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF])
            ->each(fn (User $admin) => $admin->notify(new VendorRegistrationPendingApprovalNotification($user, $tenant)));
    }
}
