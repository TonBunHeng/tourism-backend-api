<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $table = 'login_attempts';

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
        'attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'attempted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }
}
