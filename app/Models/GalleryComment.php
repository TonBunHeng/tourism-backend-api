<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryComment extends Model
{
    use HasFactory;

    protected $table = 'gallery_comments';

    protected $fillable = [
        'gallery_media_id',
        'user_id',
        'parent_id',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'gallery_media_id' => 'integer',
            'user_id' => 'integer',
            'parent_id' => 'integer',
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

    public function parent()
    {
        return $this->belongsTo(GalleryComment::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(GalleryComment::class, 'parent_id')
            ->with(['user', 'replies'])
            ->orderBy('created_at', 'asc');
    }
}
