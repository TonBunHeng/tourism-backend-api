<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'user_id',
        'place_id',
        'business_id',
        'rating',
        'title',
        'comment',
        'likes_count',
        'dislikes_count',
        'is_verified',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'place_id' => 'integer',
            'business_id' => 'integer',
            'rating' => 'integer',
            'likes_count' => 'integer',
            'dislikes_count' => 'integer',
            'is_verified' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function replies()
    {
        return $this->hasMany(ReviewReply::class, 'review_id');
    }

    public function images()
    {
        return $this->hasMany(ReviewImage::class, 'review_id');
    }
}
