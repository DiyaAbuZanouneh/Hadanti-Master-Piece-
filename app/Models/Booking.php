<?php

namespace App\Models;

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Nursery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'parent_id', 'child_id', 'nursery_id', 'classroom_id', 'type',
        'start_date', 'end_date', 'days_of_week', 'price', 'commission_rate',
        'status', 'reject_reason', 'renewed_from_id', 'accepted_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date'      => 'date',
            'end_date'        => 'date',
            'price'           => 'decimal:3',
            'commission_rate' => 'decimal:2',
            'accepted_at'     => 'datetime',
            'paid_at'         => 'datetime',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class);
    }

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function renewedFrom(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'renewed_from_id');
    }

    public function renewals(): HasMany
    {
        return $this->hasMany(Booking::class, 'renewed_from_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

}
