<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessService extends Model
{
    use HasFactory;

    protected $table = 'business_services';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'price',
        'currency',
        'duration_minutes',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'price' => 'float',
            'duration_minutes' => 'integer',
            'is_available' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
