<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $tickets = $query->latest()->paginate(10);

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'activeTab' => $request->status ?? 'all',
        ]);
    }
}
