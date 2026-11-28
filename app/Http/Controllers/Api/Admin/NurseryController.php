<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nursery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected,suspended'],
        ]);

        $nurseries = Nursery::with('owner:id,name,email,phone')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10);

        return response()->json($nurseries);
    }

    public function show(Nursery $nursery): JsonResponse
    {
        $nursery->load('owner:id,name,email,phone', 'reviewer:id,name');

        return response()->json($nursery);
    }

    public function approve(Request $request, Nursery $nursery): JsonResponse
    {
        if ($nursery->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending nurseries can be reviewed',
            ], 409);
        }

        $nursery->update([
            'status'        => 'approved',
            'reject_reason' => null,
            'reviewed_by'   => $request->user()->id,
            'reviewed_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Nursery approved',
            'nursery' => $nursery,
        ]);
    }

    public function reject(Request $request, Nursery $nursery): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($nursery->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending nurseries can be reviewed',
            ], 409);
        }

        $nursery->update([
            'status'        => 'rejected',
            'reject_reason' => $data['reason'],
            'reviewed_by'   => $request->user()->id,
            'reviewed_at'   => now(),
        ]);

        return response()->json([
            'message' => 'Nursery rejected',
            'nursery' => $nursery,
        ]);
    }
}