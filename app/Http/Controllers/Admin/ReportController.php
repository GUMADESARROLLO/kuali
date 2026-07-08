<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TicketsExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from;
        $to = $request->to;

        $query = Ticket::query()
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));

        // Metrics
        $totalTickets = (clone $query)->count();
        $resolved = (clone $query)->whereIn('status', ['resuelto', 'cerrado'])->count();
        $open = (clone $query)->where('status', 'abierto')->count();
        $inProgress = (clone $query)->where('status', 'en_proceso')->count();
        $onHold = (clone $query)->where('status', 'en_espera')->count();

        // Avg resolution time (in hours) for resolved tickets
        $avgResolution = (clone $query)->whereNotNull('resolved_at')
            ->selectRaw('avg(timestampdiff(hour, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        // Department breakdown
        $deptData = Department::orderBy('name')->get()->map(fn($d) => [
            'name' => $d->name,
            'total' => (clone $query)->where('department_id', $d->id)->count(),
        ])->filter(fn($d) => $d['total'] > 0)->values();

        $maxDeptTotal = $deptData->max('total') ?: 1;

        // Agent performance
        $agentData = User::role(['agente_it', 'admin_it'])->get()->map(fn($u) => [
            'name' => $u->name,
            'resolved' => (clone $query)->where('assigned_to', $u->id)->whereIn('status', ['resuelto', 'cerrado'])->count(),
            'total' => (clone $query)->where('assigned_to', $u->id)->count(),
        ])->filter(fn($a) => $a['total'] > 0)->sortByDesc('resolved')->values();

        return Inertia::render('Admin/Reports/Index', [
            'metrics' => [
                'totalTickets' => $totalTickets,
                'resolved' => $resolved,
                'open' => $open,
                'inProgress' => $inProgress,
                'onHold' => $onHold,
                'avgResolutionHours' => round($avgResolution ?? 0, 1),
                'resolutionRate' => $totalTickets > 0 ? round($resolved / $totalTickets * 100) : 0,
            ],
            'departments' => $deptData,
            'maxDeptTotal' => $maxDeptTotal,
            'agents' => $agentData,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->type ?? 'agentes';
        $from = $request->from;
        $to = $request->to;

        $filename = "reporte_{$type}_" . now()->format('Ymd') . '.xlsx';
        return Excel::download(new TicketsExport($type, $from, $to), $filename);
    }
}
