<?php

namespace App\Models;

use App\Models\Nursery;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password_hash', 'role', 'status',
        'must_change_password', 'work_region', 'work_address',
        'work_lat', 'work_lng',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password_hash'        => 'hashed',
            'must_change_password' => 'boolean',
            'work_lat'             => 'decimal:7',
            'work_lng'             => 'decimal:7',
        ];
    }

    public function nursery(): HasOne
    {
        return $this->hasOne(Nursery::class, 'owner_id');
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }
}
