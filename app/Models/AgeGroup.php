<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    protected $fillable = ['name','min_months','max_months','is_active'];


    protected function casts():array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}