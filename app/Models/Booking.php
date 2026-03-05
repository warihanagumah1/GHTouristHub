<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_no',
        'user_id',
        'tenant_id',
        'listing_id',
        'travelers_count',
        'special_requests',
        'service_date',
        'total_amount',
        'currency',
        'status',
        'stripe_checkout_session_id',
        'paid_at',
        'pending_payment_reminded_at',
        'upcoming_service_reminded_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'service_date' => 'date',
            'pending_payment_reminded_at' => 'datetime',
            'upcoming_service_reminded_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'booking_no';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class)->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(TenantReview::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingMessage::class)->orderBy('created_at');
    }
}
