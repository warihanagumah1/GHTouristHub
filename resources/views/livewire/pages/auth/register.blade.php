<?php

use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\VendorProfile;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $account_type = 'client';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $this->hydrateSensitiveFieldsFromRequest();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'account_type' => ['required', 'in:client,tour_company_owner,utility_owner'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['user_role'] = match ($validated['account_type']) {
            'tour_company_owner' => User::ROLE_TOUR_OWNER,
            'utility_owner' => User::ROLE_UTILITY_OWNER,
            default => User::ROLE_CLIENT,
        };
        unset($validated['account_type']);

        event(new Registered($user = User::create($validated)));

        if ($user->isVendor()) {
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
        }

        Auth::login($user);

        $this->redirect(route('verification.notice', absolute: false), navigate: true);
    }

    private function hydrateSensitiveFieldsFromRequest(): void
    {
        // Fallback for browsers/extensions that don't reliably dispatch password input sync events.
        if (filled($this->password) && filled($this->password_confirmation)) {
            return;
        }

        $components = request()->input('components');
        if (! is_array($components) || ! isset($components[0]) || ! is_array($components[0])) {
            return;
        }

        $updates = $components[0]['updates'] ?? [];
        if (! is_array($updates)) {
            return;
        }

        if (! filled($this->password) && isset($updates['password']) && is_string($updates['password'])) {
            $this->password = $updates['password'];
        }

        if (
            ! filled($this->password_confirmation)
            && isset($updates['password_confirmation'])
            && is_string($updates['password_confirmation'])
        ) {
            $this->password_confirmation = $updates['password_confirmation'];
        }
    }
}; ?>

<div>
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

    <form wire:submit.prevent="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Account Type -->
        <div class="mt-4">
            <x-input-label for="account_type" :value="__('Account Type')" />
            <x-select-input wire:model="account_type" id="account_type" name="account_type" class="mt-1 block w-full">
                <option value="client">Traveler / Client</option>
                <option value="tour_company_owner">Tour Company Owner</option>
                <option value="utility_owner">Utility Owner (Hotel/Transport/Attraction)</option>
            </x-select-input>
            <x-input-error :messages="$errors->get('account_type')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ showPassword: false }">
            <x-input-label for="password" :value="__('Password')" />

            <div class="relative mt-1">
                <x-text-input
                    x-ref="passwordInput"
                    wire:model="password"
                    id="password"
                    class="block w-full pe-20"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
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

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4" x-data="{ showConfirmPassword: false }">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <div class="relative mt-1">
                <x-text-input
                    x-ref="confirmPasswordInput"
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    class="block w-full pe-20"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <button
                    type="button"
                    class="absolute inset-y-0 right-2 my-1 rounded px-2 text-xs font-semibold text-primary/70 hover:bg-slate-100"
                    @click="
                        showConfirmPassword = !showConfirmPassword;
                        $refs.confirmPasswordInput.type = showConfirmPassword ? 'text' : 'password';
                    "
                >
                    <span x-text="showConfirmPassword ? 'Hide' : 'Show'"></span>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="fc-link" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
