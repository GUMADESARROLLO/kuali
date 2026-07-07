<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function user(Request $request): Response
    {
        $user = $request->user();
        $tickets = $user->createdTickets()
            ->latest()
            ->limit(10)
            ->get(['id', 'ticket_number', 'title', 'status', 'priority', 'created_at']);

        $stats = [
            'abiertos' => $user->createdTickets()->where('status', 'abierto')->count(),
            'en_proceso' => $user->createdTickets()->where('status', 'en_proceso')->count(),
            'resueltos' => $user->createdTickets()->whereIn('status', ['resuelto', 'cerrado'])->count(),
        ];

        return Inertia::render('User/Dashboard', [
            'stats' => $stats,
            'tickets' => $tickets,
        ]);
    }

    public function admin(Request $request): Response
    {
        $stats = [
            'abiertos' => \App\Models\Ticket::where('status', 'abierto')->count(),
            'en_proceso' => \App\Models\Ticket::where('status', 'en_proceso')->count(),
            'resueltos' => \App\Models\Ticket::whereIn('status', ['resuelto', 'cerrado'])->count(),
            'vencidos' => \App\Models\Ticket::whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['resuelto', 'cerrado', 'cancelado'])
                ->count(),
        ];

        $recent = \App\Models\Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent'])
            ->latest()
            ->limit(15)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
        ]);
    }
}
