<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryMedia extends Model
{
    use HasFactory;

    protected $table = 'gallery_media';

    protected $fillable = [
        'title',
        'type',
        'url',
        'category_id',
        'place_id',
        'file_size',
        'dimensions',
        'uploaded_by_user_id',
        'views_count',
        'likes_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'place_id' => 'integer',
            'uploaded_by_user_id' => 'integer',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function tags()
    {
        return $this->hasMany(GalleryMediaTag::class, 'media_id');
    }

    public function comments()
    {
        return $this->hasMany(GalleryComment::class, 'gallery_media_id')
            ->whereNull('parent_id')
            ->with(['user', 'replies'])
            ->orderBy('created_at', 'desc');
    }

    public function allComments()
    {
        return $this->hasMany(GalleryComment::class, 'gallery_media_id');
    }

    public function likes()
    {
        return $this->hasMany(GalleryLike::class, 'gallery_media_id');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
