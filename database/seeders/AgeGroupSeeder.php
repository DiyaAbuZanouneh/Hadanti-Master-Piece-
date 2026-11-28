<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use Illuminate\Database\Seeder;

class AgeGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            ['رضّع', 0, 12],
            ['أطفال صغار', 12, 24],
            ['تمهيدي', 24, 48],
        ];

        foreach ($groups as [$name, $min, $max]) {
            AgeGroup::updateOrCreate(
                ['name' => $name],
                ['min_months' => $min, 'max_months' => $max, 'is_active' => true]
            );
        }
    }
}