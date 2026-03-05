<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TouristRegion extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'overview',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function attractions(): HasMany
    {
        return $this->hasMany(TouristAttraction::class)->orderBy('sort_order')->orderBy('name');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }
}
