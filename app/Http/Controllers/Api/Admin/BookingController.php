<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,accepted,rejected,paid,cancelled,completed'],
            'type'   => ['nullable', 'in:daily,monthly'],
        ]);

        $bookings = Booking::with('parent:id,name', 'child:id,name', 'nursery:id,name')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(10);

        return response()->json($bookings);
    }

    public function show(Booking $booking): JsonResponse
    {
        $booking->load(
            'parent:id,name,email,phone',
            'child:id,name,birth_date',
            'nursery:id,name',
            'classroom:id,name',
            'payments:id,booking_id,amount,type,status,created_at'
        );

        return response()->json($booking);
    }
}