<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessImage extends Model
{
    use HasFactory;

    protected $table = 'business_images';

    protected $fillable = [
        'business_id',
        'image_url',
        'caption',
        'is_cover',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'is_cover' => 'boolean',
            'display_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
