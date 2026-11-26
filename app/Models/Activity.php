<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'ai_request_id', 'classroom_id', 'title', 'duration', 'skill',
        'place', 'materials', 'steps', 'scheduled_date', 'scheduled_time',
        'status', 'rating', 'done_at',
    ];

    protected function casts(): array
    {
        return [
            'materials'      => 'array',
            'steps'          => 'array',
            'scheduled_date' => 'date',
            'done_at'        => 'datetime',
        ];
    }

    public function aiRequest(): BelongsTo
    {
        return $this->belongsTo(AiRequest::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}