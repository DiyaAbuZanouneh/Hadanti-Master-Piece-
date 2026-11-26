<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'booking_id', 'amount', 'type', 'gateway_ref',
        'status', 'gateway_data', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:3',
            'gateway_data' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}