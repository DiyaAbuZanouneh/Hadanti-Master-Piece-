<?php

namespace App\Models;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nursery extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description', 'phone', 'address', 'region',
        'lat', 'lng', 'logo', 'period_name', 'start_time', 'end_time',
        'daily_price', 'monthly_price', 'status', 'reject_reason',
        'license_file', 'license_number', 'license_expiry',
        'cr_file', 'cr_number', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'lat'            => 'decimal:7',
            'lng'            => 'decimal:7',
            'daily_price'    => 'decimal:3',
            'monthly_price'  => 'decimal:3',
            'license_expiry' => 'date',
            'reviewed_at'    => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

}