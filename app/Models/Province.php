<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;

    protected $table = 'provinces';

    protected $fillable = [
        'name',
        'type',
        'population',
        'area',
        'districts_count',
        'communes_count',
        'status',
        'icon',
        'description',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'districts_count' => 'integer',
            'communes_count' => 'integer',
            'rating' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function places()
    {
        return $this->hasMany(Place::class, 'province_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'province_id');
    }
}
