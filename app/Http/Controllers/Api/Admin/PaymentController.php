<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed,refunded'],
            'type'   => ['nullable', 'in:payment,refund'],
        ]);

        $payments = Payment::select(['id', 'booking_id', 'amount', 'type', 'status', 'gateway_ref', 'created_at'])
            ->with('booking:id,parent_id,nursery_id', 'booking.parent:id,name', 'booking.nursery:id,name')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(10);

        return response()->json($payments);
    }
}