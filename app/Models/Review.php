<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
    'booking_id', 'parent_id', 'nursery_id',
    'stars', 'comment', 'reply', 'replied_at', 'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
            'is_hidden'  => 'boolean',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }
}
