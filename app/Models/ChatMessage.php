<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $table = 'chat_messages';

    public $timestamps = false;

    protected $fillable = [
        'chat_id',
        'sender_type',
        'sender_user_id',
        'message_text',
        'is_read',
        'is_ai',
    ];

    protected function casts(): array
    {
        return [
            'chat_id' => 'integer',
            'sender_user_id' => 'integer',
            'is_read' => 'boolean',
            'is_ai' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
}
