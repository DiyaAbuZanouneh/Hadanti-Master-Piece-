<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Nursery;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'totals' => [
                'users'             => User::count(),
                'nurseries'         => Nursery::count(),
                'pending_nurseries' => Nursery::where('status', 'pending')->count(),
                'open_tickets'      => Ticket::where('status', 'open')->count(),
                'bookings'          => Booking::count(),
                'revenue'           => (float) Payment::where('type', 'payment')->where('status', 'paid')->sum('amount'),
                'commission'        => (float) Booking::whereNotNull('paid_at')->sum(DB::raw('price * commission_rate / 100')),
            ],
            'users_by_role' => $this->groupCount(
                User::class,
                'role',
                ['admin', 'owner', 'teacher', 'parent']
            ),
            'nurseries_by_status' => $this->groupCount(
                Nursery::class,
                'status',
                ['pending', 'approved', 'rejected', 'suspended']
            ),
            'tickets_by_status' => $this->groupCount(
                Ticket::class,
                'status',
                ['open', 'in_progress', 'closed']
            ),
            'bookings_by_status' => $this->groupCount(
                Booking::class,
                'status',
                ['pending', 'accepted', 'rejected', 'paid', 'cancelled', 'completed']
            ),
            'bookings_by_type' => $this->groupCount(
                Booking::class,
                'type',
                ['daily', 'monthly']
            ),
            'users_per_month'   => $this->usersPerMonth(),
            'revenue_per_month' => $this->revenuePerMonth(),
        ]);
    }

    private function groupCount(string $model, string $column, array $keys): array
    {
        $counts = $model::query()
            ->selectRaw("{$column}, COUNT(*) as total")
            ->groupBy($column)
            ->pluck('total', $column);

        return collect($keys)
            ->mapWithKeys(fn ($key) => [$key => (int) ($counts[$key] ?? 0)])
            ->all();
    }

    private function usersPerMonth(): array
    {
        return collect(range(5, 0))->map(function ($i) {
            $start = now()->startOfMonth()->subMonths($i);

            return [
                'month' => $start->format('Y-m'),
                'count' => User::whereBetween('created_at', [$start, $start->copy()->endOfMonth()])->count(),
            ];
        })->all();
    }

    private function revenuePerMonth(): array
    {
        return collect(range(5, 0))->map(function ($i) {
            $start = now()->startOfMonth()->subMonths($i);

            return [
                'month' => $start->format('Y-m'),
                'total' => (float) Payment::where('type', 'payment')
                    ->where('status', 'paid')
                    ->whereBetween('created_at', [$start, $start->copy()->endOfMonth()])
                    ->sum('amount'),
            ];
        })->all();
    }
}