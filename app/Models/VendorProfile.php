<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'legal_business_name',
        'registration_number',
        'country',
        'city',
        'address_line',
        'support_phone',
        'support_email',
        'logo_url',
        'banner_url',
        'website_url',
        'founded_year',
        'about',
        'stripe_connect_account_id',
        'payout_mode',
        'tax_id',
        'verification_document_path',
        'kyc_status',
        'kyc_notes',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'founded_year' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeStorageUrl($value),
        );
    }

    protected function bannerUrl(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeStorageUrl($value),
        );
    }

    protected function normalizeStorageUrl(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        $path = (string) parse_url($value, PHP_URL_PATH);
        if ($path === '') {
            return $value;
        }

        if (str_starts_with($path, '/storage/')) {
            $normalizedPath = $path;
        } elseif (str_starts_with($path, 'storage/')) {
            $normalizedPath = '/'.$path;
        } else {
            return $value;
        }

        $query = (string) parse_url($value, PHP_URL_QUERY);
        return $query !== '' ? "{$normalizedPath}?{$query}" : $normalizedPath;
    }
}
