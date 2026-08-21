<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityAlert extends Model
{
    use HasFactory;

    protected $table = 'security_alerts';

    protected $fillable = [
        'type',
        'email',
        'ip_address',
        'attempts',
        'message',
        'is_read',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'is_read' => 'boolean',
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'is_read' => true,
        ]);
    }
}
