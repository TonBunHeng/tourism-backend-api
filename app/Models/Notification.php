<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'category',
        'title',
        'description',
        'link',
        'read',
        'read_at',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'read' => 'boolean',
            'read_at' => 'datetime',
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    public function markAsRead(): bool
    {
        return $this->update([
            'read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Create a notification record cleanly.
     */
    public static function createNotification(array $data): self
    {
        return self::create([
            'user_id' => $data['user_id'] ?? null,
            'type' => $data['type'] ?? 'system',
            'category' => $data['category'] ?? 'System',
            'title' => $data['title'] ?? 'Notification',
            'description' => $data['description'] ?? null,
            'link' => $data['link'] ?? null,
            'read' => $data['read'] ?? false,
            'read_at' => ($data['read'] ?? false) ? now() : null,
            'data' => $data['data'] ?? null,
        ]);
    }
}
