<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Models\Department;
use App\Services\TicketService;

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

    public function create(Request $request)
    {
        $departments = Department::active()->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Create', [
            'categories' => Category::active()->get(['id', 'name']),
            'departments' => $departments,
            'agents' => [],
        ]);
    }

    public function store(StoreTicketRequest $request, TicketService $service)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['department_id'] = $data['department_id'] ?? $request->user()->department_id;

        $ticket = $service->create($data, $request->file('attachments', []));

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket creado correctamente.');
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
