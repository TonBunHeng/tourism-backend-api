<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionRequest extends Model
{
    use HasFactory;

    protected $table = 'deletion_requests';

    protected $fillable = [
        'user_id',
        'request_type',
        'reason',
        'additional_info',
        'status',
        'urgency',
        'admin_notes',
        'processed_by_user_id',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'processed_by_user_id' => 'integer',
            'processed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function items()
    {
        return $this->hasMany(DeletionRequestItem::class, 'deletion_request_id');
    }
}
