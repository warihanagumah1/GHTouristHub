<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'type',
        'subtype',
        'title',
        'slug',
        'summary',
        'description',
        'city',
        'country',
        'address',
        'latitude',
        'longitude',
        'price_from',
        'currency_code',
        'pricing_unit',
        'rating_average',
        'rating_count',
        'highlights',
        'inclusions',
        'exclusions',
        'amenities',
        'languages',
        'itinerary',
        'duration_label',
        'group_size_label',
        'cancellation_policy',
        'booking_rules',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'highlights' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'amenities' => 'array',
            'languages' => 'array',
            'itinerary' => 'array',
            'price_from' => 'decimal:2',
            'rating_average' => 'decimal:2',
            'is_featured' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ListingMedia::class)->orderBy('sort_order');
    }

    public function coverMedia(): HasOne
    {
        return $this->hasOne(ListingMedia::class)->where('is_cover', true)->orderBy('sort_order');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TenantReview::class);
    }
}
