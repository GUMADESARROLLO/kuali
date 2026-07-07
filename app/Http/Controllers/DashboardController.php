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
        $query = \App\Models\Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent'])
            ->where('department_id', $user->department_id);

        $stats = [
            'abiertos'  => (clone $query)->where('status', 'abierto')->count(),
            'en_proceso' => (clone $query)->where('status', 'en_proceso')->count(),
            'resueltos' => (clone $query)->whereIn('status', ['resuelto', 'cerrado'])->count(),
            'vencidos'  => (clone $query)->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereNotIn('status', ['resuelto', 'cerrado', 'cancelado'])
                ->count(),
        ];

        $recent = (clone $query)->latest()->limit(15)->get();

        return Inertia::render('User/Dashboard', [
            'stats' => $stats,
            'recent' => $recent,
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
