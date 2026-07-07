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

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'user',
            'department',
            'category',
            'subcategory',
            'assignedAgent',
            'comments.author',
            'histories.user',
            'attachments.uploader',
        ]);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }
}
