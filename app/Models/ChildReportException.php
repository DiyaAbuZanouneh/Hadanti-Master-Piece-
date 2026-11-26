<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildReportException extends Model
{
    protected $fillable = [
        'report_id', 'child_id', 'teacher_id', 'date', 'type', 'value', 'text',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(ClassDailyReport::class, 'report_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}