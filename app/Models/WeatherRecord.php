<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'temperature',
        'humidity',
        'pressure',
        'wind_speed',
        'condition',
        'icon',
        'fetched_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function getIconUrlAttribute(): string
    {
        return "https://openweathermap.org/img/wn/{$this->icon}@2x.png";
    }
}

