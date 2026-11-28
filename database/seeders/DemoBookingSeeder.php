<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Nursery;
use App\Models\Payment;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\Concerns\SeedsDemoData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoBookingSeeder extends Seeder
{
    use SeedsDemoData;

    public function run(): void
    {
        $parents = User::where('role', 'parent')
            ->where('status', 'active')
            ->where('email', 'like', '%@demo.test')
            ->get();

        $approved = Nursery::where('status', 'approved')
            ->whereHas('owner', fn ($q) => $q->where('email', 'like', '%@demo.test'))
            ->get();

        if ($parents->isEmpty() || $approved->isEmpty()) {
            $this->command?->warn('Run DemoNurserySeeder and DemoParentSeeder first, skipping.');

            return;
        }

        if (Booking::whereIn('parent_id', $parents->pluck('id'))->exists()) {
            $this->command?->warn('Demo bookings already exist, skipping.');

            return;
        }

        $statuses = ['pending', 'accepted', 'rejected', 'paid', 'paid', 'paid', 'cancelled', 'completed'];
        $comments = [
            'حضانة ممتازة وبيئة نظيفة',
            'المعلمات متعاونات جداً',
            'الأسعار مناسبة والخدمة جيدة',
            'يحتاج تحسين في التواصل مع أولياء الأمور',
            'تجربة رائعة لطفلي',
        ];

        DB::transaction(function () use ($parents, $approved, $statuses, $comments) {
            foreach ($parents as $parent) {
                $child = $parent->children()->first();

                foreach (range(1, rand(1, 2)) as $_) {
                    $nursery = $approved->random();
                    $classroom = $nursery->classrooms()->inRandomOrder()->first();
                    $status = $statuses[array_rand($statuses)];
                    $type = rand(0, 1) ? 'monthly' : 'daily';

                    $days = (int) $parent->created_at->diffInDays(now());
                    $createdAt = now()->subDays(rand(0, $days));
                    $start = $createdAt->copy()->addDays(2);
                    $end = $type === 'monthly' ? $start->copy()->addDays(29) : $start->copy();

                    $accepted = in_array($status, ['accepted', 'paid', 'completed']);
                    $paid = in_array($status, ['paid', 'completed']);

                    $booking = Booking::create([
                        'parent_id'       => $parent->id,
                        'child_id'        => $child->id,
                        'nursery_id'      => $nursery->id,
                        'classroom_id'    => $classroom?->id,
                        'type'            => $type,
                        'start_date'      => $start->toDateString(),
                        'end_date'        => $end->toDateString(),
                        'days_of_week'    => $type === 'monthly' ? 'sun,mon,tue,wed,thu' : null,
                        'price'           => $type === 'monthly' ? $nursery->monthly_price : $nursery->daily_price,
                        'commission_rate' => 10,
                        'status'          => $status,
                        'reject_reason'   => $status === 'rejected' ? 'لا توجد أماكن شاغرة' : null,
                        'accepted_at'     => $accepted ? $this->past($createdAt->copy()->addDay()) : null,
                        'paid_at'         => $paid ? $this->past($createdAt->copy()->addDays(2)) : null,
                    ]);
                    $this->backdate($booking, $createdAt);

                    if ($paid) {
                        $this->payment($booking, 'payment', 'paid', $booking->paid_at);
                    } elseif ($status === 'accepted' && rand(0, 1)) {
                        $this->payment($booking, 'payment', 'failed', $this->past($createdAt->copy()->addDays(2)));
                    } elseif ($status === 'cancelled' && rand(0, 1)) {
                        $this->payment($booking, 'payment', 'paid', $this->past($createdAt->copy()->addDays(2)));
                        $this->payment($booking, 'refund', 'refunded', $this->past($createdAt->copy()->addDays(3)));
                    }

                    if ($paid && rand(1, 10) <= 7) {
                        $review = Review::create([
                            'booking_id' => $booking->id,
                            'parent_id'  => $parent->id,
                            'nursery_id' => $nursery->id,
                            'stars'      => fake()->randomElement([5, 5, 5, 4, 4, 3, 2, 1]),
                            'comment'    => fake()->randomElement($comments),
                            'is_hidden'  => rand(1, 10) === 1,
                        ]);
                        $this->backdate($review, $this->past($createdAt->copy()->addDays(rand(3, 10))));
                    }
                }
            }
        });
    }

    private function payment(Booking $booking, string $type, string $status, Carbon $at): void
    {
        $payment = Payment::create([
            'booking_id'  => $booking->id,
            'amount'      => $booking->price,
            'type'        => $type,
            'status'      => $status,
            'gateway_ref' => 'demo_' . Str::lower(Str::random(14)),
        ]);
        $this->backdate($payment, $at);
    }
}