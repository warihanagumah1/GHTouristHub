<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingMedia extends Model
{
    use HasFactory;

    protected $table = 'listing_media';

    protected $fillable = [
        'listing_id',
        'type',
        'url',
        'thumbnail_url',
        'alt_text',
        'caption',
        'sort_order',
        'is_cover',
    ];

    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->normalizeStorageUrl($value),
        );
    }

    protected function thumbnailUrl(): Attribute
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
