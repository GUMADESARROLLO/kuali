<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TicketsExport;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Department;
use App\Models\Person;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function assetsByPerson(Request $request)
    {
        $person = null;
        $assets = collect();
        $stats = null;

        if ($request->filled('person_id')) {
            $person = Person::with(['company', 'department'])
                ->find($request->person_id);

            if ($person) {
                $assets = Asset::with(['category', 'parent'])
                    ->where('person_id', $person->id)
                    ->orderBy('asset_tag')
                    ->get()
                    ->map(function ($asset) {
                        $assignedDate = $asset->assigned_at;
                        $purchaseDate = $asset->purchase_date;
                        return [
                            'id' => $asset->id,
                            'asset_tag' => $asset->asset_tag,
                            'name' => $asset->name,
                            'brand' => $asset->brand,
                            'model' => $asset->model,
                            'serial_number' => $asset->serial_number,
                            'status' => $asset->status,
                            'category_name' => $asset->category?->name,
                            'assigned_date' => $assignedDate ? $assignedDate->format('d M Y') : ($purchaseDate ? $purchaseDate->format('d M Y') : null),
                        ];
                    });

                $stats = [
                    'total' => $assets->count(),
                    'active' => $assets->where('status', 'asignado')->count(),
                    'maintenance' => $assets->where('status', 'en_reparacion')->count(),
                ];
            }
        }

        $people = Person::with(['company', 'department'])
            ->where('is_active', true)
            ->orderBy('last_name')->orderBy('first_name')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'first_name' => $p->first_name,
                'last_name' => $p->last_name,
                'email' => $p->email,
                'company_name' => $p->company?->name,
                'department_name' => $p->department?->name,
            ]);

        // Also pass departments for display
        $departments = Department::with('company')->get()->keyBy('id');
        $companies = \App\Models\Company::all()->keyBy('id');

        return Inertia::render('Admin/Reports/AssetsByPerson', [
            'people' => $people,
            'person' => $person ? [
                'id' => $person->id,
                'first_name' => $person->first_name,
                'last_name' => $person->last_name,
                'email' => $person->email,
                'company_name' => $person->company?->name,
                'department_name' => $person->department?->name,
            ] : null,
            'assets' => $assets,
            'stats' => $stats,
        ]);
    }

    public function exportAssetsPdf(Request $request)
    {
        if (!$request->filled('person_id')) abort(404);

        $person = Person::with(['company', 'department'])->findOrFail($request->person_id);

        $assets = Asset::with('category')
            ->where('person_id', $person->id)
            ->orderBy('asset_tag')
            ->get();

        $stats = [
            'total' => $assets->count(),
            'active' => $assets->where('status', 'asignado')->count(),
            'maintenance' => $assets->where('status', 'en_reparacion')->count(),
        ];

        $folio = 'RA-' . now()->format('Y-dm');
        $today = now()->translatedFormat('d M Y');

        $pdf = Pdf::loadView('reports.assets-by-person-pdf', compact('person', 'assets', 'stats', 'folio', 'today'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("activos-{$person->last_name}-{$person->first_name}.pdf");
    }
}
