<?php

namespace App\Models;

use App\Models\Classroom;
use App\Models\Nursery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'user_id', 'nursery_id', 'classroom_id', 'specialization',
        'qualification', 'years_experience', 'bio', 'cv_file',
        'hire_date', 'status',
    ];

    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(TeacherCertificate::class);
    }
}