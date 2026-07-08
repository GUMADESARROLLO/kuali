<?php

namespace App\Exports;

use App\Models\Ticket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TicketsExport implements FromCollection, WithHeadings, WithStyles
{
    protected string $type;
    protected ?string $from;
    protected ?string $to;

    public function __construct(string $type, ?string $from = null, ?string $to = null)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        if ($this->type === 'agentes') {
            return $this->agentReport();
        }
        return $this->departmentReport();
    }

    protected function agentReport()
    {
        return Ticket::selectRaw('assigned_to, count(*) as total,
            sum(case when status = "abierto" then 1 else 0 end) as abiertos,
            sum(case when status = "en_proceso" then 1 else 0 end) as en_proceso,
            sum(case when status = "resuelto" then 1 else 0 end) as resueltos,
            sum(case when status = "cerrado" then 1 else 0 end) as cerrados')
            ->whereNotNull('assigned_to')
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->groupBy('assigned_to')
            ->with('assignedAgent')
            ->get()
            ->map(fn($t) => [
                'Agente' => $t->assignedAgent?->name ?? '—',
                'Total' => $t->total,
                'Abiertos' => $t->abiertos,
                'En Proceso' => $t->en_proceso,
                'Resueltos' => $t->resueltos,
                'Cerrados' => $t->cerrados,
            ]);
    }

    protected function departmentReport()
    {
        return Ticket::selectRaw('department_id, count(*) as total,
            sum(case when status = "abierto" then 1 else 0 end) as abiertos,
            sum(case when status = "en_proceso" then 1 else 0 end) as en_proceso,
            sum(case when status = "resuelto" then 1 else 0 end) as resueltos,
            sum(case when status = "cerrado" then 1 else 0 end) as cerrados')
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to, fn($q) => $q->whereDate('created_at', '<=', $this->to))
            ->groupBy('department_id')
            ->with('department')
            ->get()
            ->map(fn($t) => [
                'Departamento' => $t->department?->name ?? '—',
                'Total' => $t->total,
                'Abiertos' => $t->abiertos,
                'En Proceso' => $t->en_proceso,
                'Resueltos' => $t->resueltos,
                'Cerrados' => $t->cerrados,
            ]);
    }

    public function headings(): array
    {
        return $this->type === 'agentes'
            ? ['Agente', 'Total', 'Abiertos', 'En Proceso', 'Resueltos', 'Cerrados']
            : ['Departamento', 'Total', 'Abiertos', 'En Proceso', 'Resueltos', 'Cerrados'];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
