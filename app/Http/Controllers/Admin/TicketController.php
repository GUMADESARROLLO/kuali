<?php

namespace App\Http\Controllers\Admin;

use App\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TicketCommentRequest;
use App\Models\Category;
use App\Models\Department;
use App\Models\Subcategory;
use App\Models\TicketComment;
use App\Models\TicketHistory;
use App\Models\User;
use App\Services\TicketService;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'department', 'category', 'subcategory', 'assignedAgent']);

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('ticket_number', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->assigned === 'no') {
            $query->whereNull('assigned_to');
        }

        $perPage = in_array((int) $request->per_page, [10, 25, 50]) ? (int) $request->per_page : 10;
        $tickets = $query->latest()->paginate($perPage);

        $unassignedCount = Ticket::whereNull('assigned_to')->count();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'activeTab' => $request->assigned === 'no' ? 'unassigned' : ($request->status ?? 'all'),
            'activePerPage' => $perPage,
            'unassignedCount' => $unassignedCount,
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
            'histories.user',
            'attachments.uploader',
            'comments.author',
            'comments.attachments',
        ]);

        $ticket->setRelation('comments', $ticket->comments->sortBy('created_at')->values());

        $agents = User::role(['agente_it', 'admin_it'])->get(['id', 'name']);
        $categories = Category::active()->get(['id', 'name']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
            'agents' => $agents,
            'categories' => $categories,
        ]);
    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['agente_it', 'admin_it'])) {
            abort(403, 'Solo agentes y administradores pueden asignar tickets.');
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $agent = User::role(['agente_it', 'admin_it'])->findOrFail($request->assigned_to);

        $ticket->update([
            'assigned_to' => $agent->id,
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action' => $ticket->wasChanged('assigned_to') ? TicketHistory::ACTION_REASSIGNED : TicketHistory::ACTION_ASSIGNED,
            'description' => 'Asignado a ' . $agent->name,
        ]);

        return redirect()->back()->with('success', 'Ticket asignado a ' . $agent->name);
    }

    public function comment(TicketCommentRequest $request, Ticket $ticket, TicketService $service)
    {
        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment,
            'is_internal' => $request->boolean('is_internal'),
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action' => TicketHistory::ACTION_COMMENTED,
            'description' => 'Comentario agregado por ' . $request->user()->name,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $service->uploadAttachment($ticket, $file, $request->user()->id, $comment->id);
        }

        return redirect()->route('admin.tickets.show', $ticket)->with('success', 'Comentario agregado.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['agente_it', 'admin_it'])) {
            abort(403, 'Solo agentes y administradores pueden cambiar el estado.');
        }

        $validated = $request->validate([
            'status' => 'required|in:abierto,en_proceso,en_espera,resuelto,cerrado,cancelado',
        ]);

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        $data = ['status' => $newStatus];

        if ($newStatus === 'resuelto' && !$ticket->resolved_at) {
            $data['resolved_at'] = now();
        }
        if ($newStatus === 'cerrado' && !$ticket->closed_at) {
            $data['closed_at'] = now();
        }

        $ticket->update($data);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action' => TicketHistory::ACTION_STATUS_CHANGED,
            'description' => "Estado cambiado de '{$oldStatus}' a '{$newStatus}'",
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Estado actualizado a '{$newStatus}'.");
    }

    public function updatePriority(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['agente_it', 'admin_it'])) {
            abort(403);
        }

        $validated = $request->validate([
            'priority' => 'required|in:baja,media,alta,urgente',
        ]);

        $ticket->update(['priority' => $validated['priority']]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', "Prioridad actualizada.");
    }

    public function reclassify(Request $request, Ticket $ticket)
    {
        if (!$request->user()->hasAnyRole(['agente_it', 'admin_it'])) {
            abort(403);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'required|exists:subcategories,id',
        ]);

        $old = "{$ticket->category?->name} / {$ticket->subcategory?->name}";
        $newCat = Category::find($validated['category_id']);
        $newSub = Subcategory::find($validated['subcategory_id']);

        $ticket->update([
            'category_id' => $validated['category_id'],
            'subcategory_id' => $validated['subcategory_id'],
        ]);

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action' => TicketHistory::ACTION_RECLASSIFIED,
            'description' => "Reclasificado de '{$old}' a '{$newCat?->name} / {$newSub?->name}'",
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket reclasificado.');
    }

    public function resolve(TicketCommentRequest $request, Ticket $ticket, TicketService $service)
    {
        if (!$request->user()->hasAnyRole(['agente_it', 'admin_it'])) {
            abort(403);
        }

        $comment = TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'comment' => $request->comment ?: 'Ticket resuelto.',
            'is_internal' => false,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $service->uploadAttachment($ticket, $file, $request->user()->id, $comment->id);
        }

        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action' => TicketHistory::ACTION_RESOLVED,
            'description' => 'Ticket resuelto por ' . $request->user()->name . ': ' . $request->comment,
        ]);

        $ticket->update([
            'status' => 'resuelto',
            'resolved_at' => now(),
        ]);

        return redirect()->route('admin.tickets.show', $ticket)
            ->with('success', 'Ticket resuelto.');
    }
}
