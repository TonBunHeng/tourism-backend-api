<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $table = 'businesses';

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'category_id',
        'province_id',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'website',
        'price_range',
        'status',
        'verification_status',
        'verified_at',
        'verified_by',
        'rejection_reason',
        'rating',
        'review_count',
    ];

    protected function casts(): array
    {
        return [
            'owner_id' => 'integer',
            'category_id' => 'integer',
            'province_id' => 'integer',
            'verified_by' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'rating' => 'float',
            'review_count' => 'integer',
            'verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($business) {
            if (empty($business->slug) && !empty($business->name)) {
                $baseSlug = Str::slug($business->name);
                $slug = $baseSlug;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
                $business->slug = $slug;
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BusinessImage::class, 'business_id')->orderBy('display_order', 'asc');
    }

    public function services(): HasMany
    {
        return $this->hasMany(BusinessService::class, 'business_id');
    }

    public function hours(): HasMany
    {
        return $this->hasMany(BusinessHour::class, 'business_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(BusinessPromotion::class, 'business_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'business_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'business_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('verification_status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('verification_status', 'approved')
                     ->where('status', 'active');
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        $cover = $this->images->firstWhere('is_cover', true) ?? $this->images->first();
        return $cover ? $cover->image_url : null;
    }

    public function recalculateRating(): void
    {
        $stats = $this->reviews()->where('status', 'Approved')->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')->first();
        $this->rating = $stats && $stats->avg_rating ? round((float)$stats->avg_rating, 2) : 0.00;
        $this->review_count = $stats && $stats->count ? (int)$stats->count : 0;
        $this->saveQuietly();
    }
}
