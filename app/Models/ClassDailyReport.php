<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassDailyReport extends Model
{
    protected $fillable = [
        'classroom_id', 'teacher_id', 'date', 'breakfast', 'lunch',
        'nap_from', 'nap_to', 'mood', 'activities_text', 'general_note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}