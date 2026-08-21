<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_by',
        'is_active',
        'blocked_at',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public static function isBlocked(string $ip): bool
    {
        return static::where('ip_address', $ip)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}
