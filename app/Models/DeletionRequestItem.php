<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletionRequestItem extends Model
{
    use HasFactory;

    protected $table = 'deletion_request_items';

    public $timestamps = false;

    protected $fillable = [
        'deletion_request_id',
        'item_type',
        'item_id',
        'item_name',
        'category',
        'date_added',
    ];

    protected function casts(): array
    {
        return [
            'deletion_request_id' => 'integer',
            'item_id' => 'integer',
            'date_added' => 'date:Y-m-d',
            'created_at' => 'datetime',
        ];
    }

    public function deletionRequest()
    {
        return $this->belongsTo(DeletionRequest::class, 'deletion_request_id');
    }
}
