<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'role'   => ['nullable', 'in:admin,owner,teacher,parent'],
            'status' => ['nullable', 'in:active,inactive,suspended'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $users = User::query()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return response()->json($users);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot change your own status',
            ], 403);
        }

        $user->update(['status' => $data['status']]);

        if ($data['status'] !== 'active') {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'User status updated',
            'user'    => $user,
        ]);
    }
}