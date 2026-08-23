<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryLike extends Model
{
    use HasFactory;

    protected $table = 'gallery_likes';

    protected $fillable = [
        'gallery_media_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'gallery_media_id' => 'integer',
            'user_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function media()
    {
        return $this->belongsTo(GalleryMedia::class, 'gallery_media_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
