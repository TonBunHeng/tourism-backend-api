<?php

namespace App\Models;

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
