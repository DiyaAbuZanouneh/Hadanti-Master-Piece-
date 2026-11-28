<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            AgeGroupSeeder::class,
        ]);

        if (! app()->isProduction()) {
            $this->call([
                DemoNurserySeeder::class,
                DemoParentSeeder::class,
                DemoBookingSeeder::class,
                DemoTicketSeeder::class,
            ]);
        }
    }
}