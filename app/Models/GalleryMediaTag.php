<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryMediaTag extends Model
{
    use HasFactory;

    protected $table = 'gallery_media_tags';

    public $timestamps = false;

    protected $fillable = [
        'media_id',
        'tag_name',
    ];

    protected function casts(): array
    {
        return [
            'media_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function media()
    {
        return $this->belongsTo(GalleryMedia::class, 'media_id');
    }
}
