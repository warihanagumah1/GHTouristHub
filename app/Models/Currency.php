<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate_from_usd',
        'is_default',
        'is_active',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'rate_from_usd' => 'decimal:6',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'last_synced_at' => 'datetime',
        ];
    }
}
