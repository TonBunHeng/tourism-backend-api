<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripItinerary extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'place_id',
        'day_number',
        'time_slot',
        'activity',
        'estimated_cost',
        'duration_minutes',
        'notes',
        'sort_order',
        'is_completed',
    ];

    protected $casts = [
        'day_number' => 'integer',
        'estimated_cost' => 'decimal:2',
        'duration_minutes' => 'integer',
        'sort_order' => 'integer',
        'is_completed' => 'boolean',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
}
