<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewImage extends Model
{
    use HasFactory;

    protected $table = 'review_images';

    public $timestamps = false;

    protected $fillable = [
        'review_id',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'review_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function review()
    {
        return $this->belongsTo(Review::class, 'review_id');
    }
}
