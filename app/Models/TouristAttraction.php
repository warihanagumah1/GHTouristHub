<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TouristAttraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'tourist_region_id',
        'name',
        'slug',
        'city',
        'address',
        'summary',
        'description',
        'hero_image_url',
        'gallery_images',
        'visiting_hours',
        'entry_fee',
        'best_time_to_visit',
        'contact_info',
        'website_url',
        'how_to_get_there',
        'travel_tips',
        'safety_notes',
        'featured_activities',
        'nearby_places',
        'is_featured',
        'sort_order',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'gallery_images' => 'array',
            'featured_activities' => 'array',
            'nearby_places' => 'array',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(TouristRegion::class, 'tourist_region_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
