<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;

    protected $table = 'places';

    protected $fillable = [
        'name',
        'category_id',
        'province_id',
        'address',
        'coordinates',
        'latitude',
        'longitude',
        'description',
        'best_time',
        'duration',
        'price',
        'rating',
        'reviews_count',
        'visitors_count',
        'image_url',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'province_id' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'rating' => 'float',
            'reviews_count' => 'integer',
            'visitors_count' => 'integer',
            'is_featured' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'place_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'place_id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'place_id');
    }

    public function galleryMedia()
    {
        return $this->hasMany(GalleryMedia::class, 'place_id');
    }
}
