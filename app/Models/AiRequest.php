<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRequest extends Model
{
    protected $fillable = [
        'teacher_id', 'classroom_id', 'scope', 'params',
        'prompt', 'response', 'status', 'error',
    ];

    protected function casts(): array
    {
        return [
            'params' => 'array',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }
}