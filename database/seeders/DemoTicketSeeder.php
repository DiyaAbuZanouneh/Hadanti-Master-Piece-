<?php

namespace Database\Seeders;

use App\Models\Nursery;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoTicketSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $parents = User::where('role', 'parent')->where('email', 'like', '%@demo.test')->get();
        $nurseries = Nursery::where('status', 'approved')->get();

        if ($parents->isEmpty() || $nurseries->isEmpty()) {
            $this->command?->warn('Run the other demo seeders first, skipping.');

            return;
        }

        if (Ticket::whereIn('user_id', $parents->pluck('id'))->exists()) {
            $this->command?->warn('Demo tickets already exist, skipping.');

            return;
        }

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $descriptions = [
            'تأخر في استلام الطفل من الحضانة.',
            'أرغب بالاستفسار عن مواعيد الدوام في الأعياد.',
            'اقتراح إضافة تطبيق لمتابعة وجبات الطفل.',
            'مشكلة في عملية الدفع ولم يتم تأكيد الحجز.',
            'النظافة في الفصل تحتاج إلى متابعة.',
        ];
        $categories = ['خدمة', 'دفع', 'حجز', 'نظافة'];

        DB::transaction(function () use ($parents, $nurseries, $admin, $descriptions, $categories) {
            foreach (range(1, 10) as $_) {
                $status = fake()->randomElement(['open', 'open', 'in_progress', 'closed']);
                $createdAt = now()->subDays(rand(0, 120));

                $ticket = Ticket::create([
                    'user_id'     => $parents->random()->id,
                    'nursery_id'  => $nurseries->random()->id,
                    'type'        => fake()->randomElement(['complaint', 'inquiry', 'suggestion']),
                    'category'    => fake()->randomElement($categories),
                    'description' => fake()->randomElement($descriptions),
                    'status'      => $status,
                    'admin_note'  => $status === 'open' ? null : 'تمت متابعة الموضوع مع الحضانة.',
                    'closed_by'   => $status === 'closed' ? $admin->id : null,
                    'closed_at'   => $status === 'closed' ? $this->past($createdAt->copy()->addDays(3)) : null,
                ]);
                $this->backdate($ticket, $createdAt);
            }
        });
    }
}