<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTicketRequest;
use App\Models\Category;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    public function create(Request $request)
    {
        return Inertia::render('User/Tickets/Create', [
            'categories' => Category::active()->get(['id', 'name']),
        ]);
    }

    public function store(StoreTicketRequest $request, TicketService $service)
    {
        $user = $request->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        $data['department_id'] = $user->department_id;

        $ticket = $service->create($data, $request->file('attachments', []));

        return redirect()->route('user.dashboard')
            ->with('success', 'Ticket creado correctamente.');
    }
}
