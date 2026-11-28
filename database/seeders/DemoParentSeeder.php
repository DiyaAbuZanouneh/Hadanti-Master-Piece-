<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoParentSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        if (User::where('email', 'parent1@demo.test')->exists()) {
            $this->command?->warn('Demo parents already exist, skipping.');

            return;
        }

        DB::transaction(function () {
            foreach (range(1, 12) as $n) {
                $createdAt = now()->subDays(rand(0, 170));
                $status = $n === 11 ? 'inactive' : ($n === 12 ? 'suspended' : 'active');

                $parent = $this->makeUser(
                    fake('ar_SA')->name(),
                    "parent{$n}@demo.test",
                    'parent',
                    $createdAt,
                    $status
                );

                Child::create([
                    'parent_id'  => $parent->id,
                    'name'       => fake('ar_SA')->firstName(),
                    'birth_date' => now()->subMonths(rand(6, 48))->toDateString(),
                    'gender'     => fake()->randomElement(['male', 'female']),
                ]);
            }
        });
    }
}