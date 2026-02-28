<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_ADMIN_STAFF = 'admin_staff';
    public const ROLE_TOUR_OWNER = 'tour_company_owner';
    public const ROLE_TOUR_STAFF = 'tour_company_staff';
    public const ROLE_UTILITY_OWNER = 'utility_owner';
    public const ROLE_UTILITY_STAFF = 'utility_staff';
    public const ROLE_CLIENT = 'client';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_role',
        'is_blocked',
        'blocked_at',
        'social_provider',
        'social_provider_id',
        'avatar_url',
        'two_factor_enabled',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_expires_at' => 'datetime',
            'is_blocked' => 'boolean',
            'blocked_at' => 'datetime',
        ];
    }

    /**
     * Determine whether account access is currently blocked by admin.
     */
    public function isBlocked(): bool
    {
        return (bool) $this->is_blocked;
    }

    /**
     * Resolve the primary dashboard route name for this user.
     */
    public function dashboardRoute(): string
    {
        return match ($this->user_role) {
            self::ROLE_ADMIN,
            self::ROLE_ADMIN_STAFF => 'admin.dashboard',
            self::ROLE_TOUR_OWNER,
            self::ROLE_TOUR_STAFF,
            self::ROLE_UTILITY_OWNER,
            self::ROLE_UTILITY_STAFF => 'vendor.dashboard',
            default => 'client.dashboard',
        };
    }

    /**
     * Determine if the user belongs to any vendor role.
     */
    public function isVendor(): bool
    {
        return in_array($this->user_role, [
            self::ROLE_TOUR_OWNER,
            self::ROLE_TOUR_STAFF,
            self::ROLE_UTILITY_OWNER,
            self::ROLE_UTILITY_STAFF,
        ], true);
    }

    /**
     * Generate and persist a one-time 2FA code for this user.
     */
    public function issueTwoFactorCode(): string
    {
        $code = (string) random_int(100000, 999999);

        $this->forceFill([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => now()->addMinutes((int) config('auth.two_factor.code_expiration_minutes', 10)),
        ])->save();

        return $code;
    }

    /**
     * Validate a one-time 2FA code.
     */
    public function hasValidTwoFactorCode(string $code): bool
    {
        if (! $this->two_factor_code || ! $this->two_factor_expires_at) {
            return false;
        }

        return $this->two_factor_expires_at->isFuture() && Hash::check(trim($code), $this->two_factor_code);
    }

    /**
     * Clear stored 2FA challenge values.
     */
    public function clearTwoFactorCode(): void
    {
        $this->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();
    }

    /**
     * Tenants directly owned by this user.
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_user_id');
    }

    /**
     * Tenant memberships for this user.
     */
    public function tenantMemberships(): HasMany
    {
        return $this->hasMany(TenantMember::class);
    }

    /**
     * Bookings made by this user.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class, 'requested_by_user_id');
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function supportTicketComments(): HasMany
    {
        return $this->hasMany(SupportTicketComment::class);
    }

    /**
     * Return the primary tenant a vendor user should manage.
     */
    public function primaryTenant(): ?Tenant
    {
        $owned = $this->ownedTenants()->first();

        if ($owned) {
            return $owned;
        }

        $membership = $this->tenantMemberships()->where('is_active', true)->first();

        return $membership?->tenant;
    }
}
