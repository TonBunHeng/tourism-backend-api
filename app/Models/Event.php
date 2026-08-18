<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'category',
        'description',
        'location',
        'place_id',
        'province_id',
        'start_date',
        'end_date',
        'start_time',
        'attendees_count',
        'price',
        'organizer',
        'featured',
        'rating',
        'image_url',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'place_id' => 'integer',
            'province_id' => 'integer',
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'attendees_count' => 'integer',
            'featured' => 'boolean',
            'rating' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Compute dynamic status based on start_date and end_date.
     */
    public function getComputedStatusAttribute(): string
    {
        $rawStatus = $this->attributes['status'] ?? 'Upcoming';
        if ($rawStatus === 'Cancelled') {
            return 'Cancelled';
        }

        if (!$this->start_date) {
            return $rawStatus ?: 'Upcoming';
        }

        $today = Carbon::today();
        $start = Carbon::parse($this->start_date)->startOfDay();
        $end = $this->end_date ? Carbon::parse($this->end_date)->endOfDay() : $start->copy()->endOfDay();

        if ($today->lt($start)) {
            return 'Upcoming';
        } elseif ($today->gt($end)) {
            return 'Completed';
        } else {
            return 'Ongoing';
        }
    }

    public static function autoStatusFor(?string $startDate, ?string $endDate, ?string $manualStatus = null): string
    {
        if ($manualStatus === 'Cancelled') {
            return 'Cancelled';
        }

        if (!$startDate) {
            return $manualStatus ?: 'Upcoming';
        }

        $today = Carbon::today();
        $start = Carbon::parse($startDate)->startOfDay();
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : $start->copy()->endOfDay();

        if ($today->lt($start)) {
            return 'Upcoming';
        } elseif ($today->gt($end)) {
            return 'Completed';
        } else {
            return 'Ongoing';
        }
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function tags()
    {
        return $this->hasMany(EventTag::class, 'event_id');
    }
}
