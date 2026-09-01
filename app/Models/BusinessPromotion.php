<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPromotion extends Model
{
    use HasFactory;

    protected $table = 'business_promotions';

    protected $fillable = [
        'business_id',
        'title',
        'description',
        'discount_percentage',
        'discount_amount',
        'promo_code',
        'start_date',
        'end_date',
        'is_active',
        'banner_url',
    ];

    protected function casts(): array
    {
        return [
            'business_id' => 'integer',
            'discount_percentage' => 'float',
            'discount_amount' => 'float',
            'is_active' => 'boolean',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            });
    }
}
