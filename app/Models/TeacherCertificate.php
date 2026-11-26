<?php

namespace App\Models;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherCertificate extends Model
{
    protected $fillable = [
        'teacher_id', 'title', 'issuer', 'issue_date', 'expiry_date', 'file',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}