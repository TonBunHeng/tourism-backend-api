<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'description',
        'color',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function places()
    {
        return $this->hasMany(Place::class, 'category_id');
    }

    public function galleryMedia()
    {
        return $this->hasMany(GalleryMedia::class, 'category_id');
    }
}
