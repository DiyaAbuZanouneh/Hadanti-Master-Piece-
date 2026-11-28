<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'stars'  => ['nullable', 'integer', 'between:1,5'],
            'hidden' => ['nullable', 'in:0,1'],
        ]);

        $reviews = Review::with('parent:id,name', 'nursery:id,name')
            ->when($request->filled('stars'), fn ($q) => $q->where('stars', $request->stars))
            ->when($request->filled('hidden'), fn ($q) => $q->where('is_hidden', $request->hidden === '1'))
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function updateVisibility(Request $request, Review $review): JsonResponse
    {
        $data = $request->validate([
            'is_hidden' => ['required', 'boolean'],
        ]);

        $review->update(['is_hidden' => $data['is_hidden']]);

        return response()->json([
            'message' => 'Review visibility updated',
            'review'  => $review,
        ]);
    }
}