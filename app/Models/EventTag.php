<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventTag extends Model
{
    use HasFactory;

    protected $table = 'event_tags';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'tag_name',
    ];

    protected function casts(): array
    {
        return [
            'event_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
