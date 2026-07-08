<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function user(Request $request): Response
    {
        $user = $request->user();
        $query = Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent'])
            ->where('department_id', $user->department_id);

        $stats = [
            'abiertos'   => (clone $query)->where('status', 'abierto')->count(),
            'en_proceso' => (clone $query)->where('status', 'en_proceso')->count(),
            'resueltos'  => (clone $query)->whereIn('status', ['resuelto', 'cerrado'])->count(),
            'vencidos'   => (clone $query)->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['resuelto', 'cerrado', 'cancelado'])
                ->count(),
        ];

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = in_array((int) $request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $tickets = $query->latest()->paginate($perPage);

        return Inertia::render('User/Dashboard', [
            'stats' => $stats,
            'tickets' => $tickets,
        ]);
    }

    public function admin(Request $request): Response
    {
        $stats = [
            'abiertos'   => Ticket::where('status', 'abierto')->count(),
            'en_proceso' => Ticket::where('status', 'en_proceso')->count(),
            'resueltos'  => Ticket::whereIn('status', ['resuelto', 'cerrado'])->count(),
            'vencidos'   => Ticket::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['resuelto', 'cerrado', 'cancelado'])
                ->count(),
        ];

        $recent = Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent'])
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}
