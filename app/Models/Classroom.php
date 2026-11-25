<?php

namespace App\Models;

use App\Models\AgeGroup;
use App\Models\Nursery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classroom extends Model
{
    protected $fillable = [
        'nursery_id', 'name', 'age_group_id', 'capacity',
    ];

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }

    public function ageGroup(): BelongsTo
    {
        return $this->belongsTo(AgeGroup::class);
    }
}