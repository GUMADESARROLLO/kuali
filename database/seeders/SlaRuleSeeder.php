<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\SlaRule;
use Illuminate\Database\Seeder;

class SlaRuleSeeder extends Seeder
{
    public function run(): void
    {
        $calendarId = Calendar::first()->id ?? 1;

        SlaRule::upsert([
            [
                'name' => 'Urgente',
                'description' => 'Tickets con prioridad urgente — respuesta en 1h, solución en 8h hábiles',
                'calendar_id' => $calendarId,
                'conditions' => json_encode(['priority' => ['urgente']]),
                'first_response_hours' => 1,
                'update_hours' => 4,
                'solution_hours' => 8,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Alta',
                'description' => 'Tickets con prioridad alta — respuesta en 4h, solución en 24h hábiles',
                'calendar_id' => $calendarId,
                'conditions' => json_encode(['priority' => ['alta']]),
                'first_response_hours' => 4,
                'update_hours' => 8,
                'solution_hours' => 24,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Media',
                'description' => 'Tickets con prioridad media — respuesta en 8h, solución en 48h hábiles',
                'calendar_id' => $calendarId,
                'conditions' => json_encode(['priority' => ['media']]),
                'first_response_hours' => 8,
                'update_hours' => 24,
                'solution_hours' => 48,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Baja',
                'description' => 'Tickets con prioridad baja — respuesta en 24h, solución en 72h hábiles',
                'calendar_id' => $calendarId,
                'conditions' => json_encode(['priority' => ['baja']]),
                'first_response_hours' => 24,
                'update_hours' => 48,
                'solution_hours' => 72,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Sin SLA (Compras/Cotizaciones)',
                'description' => 'Tickets de compras/cotizaciones — sin SLA. Aplica cuando no hay prioridad definida o categoría específica.',
                'calendar_id' => $calendarId,
                'conditions' => json_encode([]),
                'first_response_hours' => 0,
                'update_hours' => 0,
                'solution_hours' => 0,
                'sort_order' => 99,
                'is_active' => true,
            ],
        ], ['sort_order'], ['name', 'description', 'calendar_id', 'conditions', 'first_response_hours', 'update_hours', 'solution_hours', 'is_active']);
    }
}
