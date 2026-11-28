<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\Classroom;
use App\Models\Nursery;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoNurserySeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        if (User::where('email', 'owner1@demo.test')->exists()) {
            $this->command?->warn('Demo nurseries already exist, skipping.');

            return;
        }

        $this->call(AgeGroupSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();
        $groups = AgeGroup::orderBy('min_months')->get();

        $names = ['الأمل', 'النور', 'البراعم', 'الزهور', 'النجوم', 'السنابل'];
        $statuses = ['approved', 'approved', 'approved', 'pending', 'pending', 'rejected'];
        $regions = ['عمّان', 'إربد', 'الزرقاء', 'العقبة', 'السلط', 'مادبا'];

        DB::transaction(function () use ($admin, $groups, $names, $statuses, $regions) {
            foreach ($names as $i => $name) {
                $status = $statuses[$i];
                $createdAt = now()->subDays(rand(30, 170));

                $owner = $this->makeUser(
                    fake('ar_SA')->name(),
                    'owner' . ($i + 1) . '@demo.test',
                    'owner',
                    $createdAt
                );

                $nursery = Nursery::create([
                    'owner_id'       => $owner->id,
                    'name'           => 'حضانة ' . $name,
                    'description'    => 'بيئة آمنة ومحفّزة للأطفال مع كادر مؤهل.',
                    'phone'          => $this->phone(),
                    'address'        => 'شارع الجامعة، ' . $regions[$i],
                    'region'         => $regions[$i],
                    'lat'            => fake()->latitude(31.8, 32.1),
                    'lng'            => fake()->longitude(35.8, 36.0),
                    'period_name'    => 'الفترة الصباحية',
                    'start_time'     => '07:30',
                    'end_time'       => '14:30',
                    'daily_price'    => fake()->randomFloat(3, 5, 10),
                    'monthly_price'  => fake()->randomFloat(3, 80, 150),
                    'status'         => $status,
                    'reject_reason'  => $status === 'rejected' ? 'الترخيص منتهي أو غير واضح' : null,
                    'license_number' => 'LIC-' . fake()->numerify('#####'),
                    'license_expiry' => now()->addMonths(rand(6, 24))->toDateString(),
                    'cr_number'      => 'CR-' . fake()->numerify('#####'),
                    'reviewed_by'    => $status === 'pending' ? null : $admin->id,
                    'reviewed_at'    => $status === 'pending' ? null : $this->past($createdAt->copy()->addDays(2)),
                ]);
                $this->backdate($nursery, $createdAt);

                if ($status !== 'approved') {
                    continue;
                }

                foreach (['الفصل الأول', 'الفصل الثاني'] as $j => $className) {
                    $classroom = Classroom::create([
                        'nursery_id'   => $nursery->id,
                        'name'         => $className,
                        'age_group_id' => $groups[($i + $j) % $groups->count()]->id,
                        'capacity'     => 15,
                    ]);

                    $teacher = $this->makeUser(
                        fake('ar_SA')->name(),
                        "teacher{$i}{$j}@demo.test",
                        'teacher',
                        $this->past($createdAt->copy()->addDay())
                    );

                    Teacher::create([
                        'user_id'          => $teacher->id,
                        'nursery_id'       => $nursery->id,
                        'classroom_id'     => $classroom->id,
                        'specialization'   => 'تربية الطفل',
                        'qualification'    => 'بكالوريوس',
                        'years_experience' => rand(1, 10),
                        'hire_date'        => $createdAt->toDateString(),
                        'status'           => 'active',
                    ]);
                }
            }
        });
    }
}