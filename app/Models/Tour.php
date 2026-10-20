<?php

namespace App\Models;

use App\Traits\NormalizesMediaUrl;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Tour extends Model
{
    use HasFactory, NormalizesMediaUrl;

    protected $table = 'tours';

    protected $fillable = [
        'title',
        'slug',
        'destination',
        'province_id',
        'province',
        'duration',
        'duration_days',
        'price',
        'currency',
        'rating',
        'reviews_count',
        'badge',
        'category',
        'image_url',
        'description',
        'highlights',
        'itinerary',
        'included',
        'max_travelers',
        'group_size',
        'difficulty',
        'status',
    ];

    protected $casts = [
        'highlights' => 'array',
        'itinerary' => 'array',
        'included' => 'array',
        'price' => 'float',
        'rating' => 'float',
        'reviews_count' => 'integer',
        'duration_days' => 'integer',
        'max_travelers' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tour) {
            if (empty($tour->slug)) {
                $tour->slug = Str::slug($tour->title) . '-' . Str::random(5);
            }
        });
    }

    public function provinceRelation(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function getImageUrlAttribute($value): ?string
    {
        return $this->normalizeUrl($value);
    }
}
