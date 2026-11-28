<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTicketRequest;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'status' => ['nullable', 'in:open,in_progress,closed'],
            'type'   => ['nullable', 'in:complaint,inquiry,suggestion'],
        ]);

        $tickets = Ticket::with('user:id,name,email', 'nursery:id,name')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->paginate(10);

        return response()->json($tickets);
    }

    public function show(Ticket $ticket): JsonResponse
    {
        $ticket->load('user:id,name,email,phone', 'nursery:id,name', 'closer:id,name');

        return response()->json($ticket);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $data = $request->validated();

        if ($data['status'] === 'closed') {
            $closedBy = $ticket->closed_by ?? $request->user()->id;
            $closedAt = $ticket->closed_at ?? now();
        } else {
            $closedBy = null;
            $closedAt = null;
        }

        $ticket->update([
            'status'     => $data['status'],
            'admin_note' => $data['admin_note'] ?? null,
            'closed_by'  => $closedBy,
            'closed_at'  => $closedAt,
        ]);

        return response()->json([
            'message' => 'Ticket updated',
            'ticket'  => $ticket,
        ]);
    }
}