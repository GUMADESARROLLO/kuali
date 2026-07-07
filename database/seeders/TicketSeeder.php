<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Subcategory;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::whereIn('slug', [
            'recursos-humanos',
            'contabilidad',
            'atencion-al-cliente-sac',
        ])->get()->keyBy('slug');

        $users = User::whereIn('email', [
            'usuario@kuali.test',
            'contabilidad@kuali.test',
            'sac@kuali.test',
        ])->get()->keyBy('email');

        $agent = User::where('email', 'agente@kuali.test')->first();
        $admin = User::where('email', 'admin@kuali.test')->first();

        $subcategories = Subcategory::with('category')->get();

        $ticketCounts = [
            'recursos-humanos' => 27,
            'contabilidad' => 77,
            'atencion-al-cliente-sac' => 29,
        ];

        $statuses = [
            'abierto', 'abierto', 'abierto',
            'en_proceso', 'en_proceso',
            'en_espera',
            'resuelto', 'resuelto', 'resuelto',
            'cerrado', 'cerrado',
        ];

        $priorities = ['baja', 'media', 'media', 'alta', 'alta', 'urgente'];

        $titleTemplates = [
            'No puedo acceder a %s',
            'Error al abrir %s',
            'Solicitud de instalacion de %s',
            'Problema con %s',
            'Actualizacion fallida de %s',
            'Configuracion incorrecta de %s',
            'Se requiere permiso para %s',
            'Bloqueo en %s',
            'Rendimiento lento en %s',
            'Error inesperado en %s',
        ];

        $descriptionTemplates = [
            'Al intentar usar el sistema se presenta un error que impide continuar con mis labores diarias. Requiero asistencia urgente.',
            'Desde esta mañana no funciona correctamente. Ya intente reiniciar pero el problema persiste.',
            'Necesito que instalen la ultima version disponible para poder completar mi trabajo.',
            'El equipo no reconoce el dispositivo y no he podido encontrar una solucion en la documentacion.',
            'Se solicita la creacion de una cuenta con los permisos necesarios para el nuevo integrante del equipo.',
            'La aplicacion se cierra inesperadamente cada vez que intento cargar el modulo de reportes.',
            'No recibo correos electronicos desde ayer. Revisaron del lado del servidor y dicen que todo esta bien.',
            'El sistema de facturacion no calcula correctamente los impuestos en las facturas internacionales.',
        ];

        $now = now();

        foreach ($ticketCounts as $deptSlug => $count) {
            $dept = $departments->get($deptSlug);
            $deptUser = match ($deptSlug) {
                'recursos-humanos' => $users->get('usuario@kuali.test'),
                'contabilidad' => $users->get('contabilidad@kuali.test'),
                'atencion-al-cliente-sac' => $users->get('sac@kuali.test'),
            };

            if (!$dept || !$deptUser) continue;

            for ($i = 1; $i <= $count; $i++) {
                $subcategory = $subcategories->random();
                $status = $statuses[array_rand($statuses)];
                $priority = $priorities[array_rand($priorities)];
                $titleTemplate = $titleTemplates[array_rand($titleTemplates)];
                $description = $descriptionTemplates[array_rand($descriptionTemplates)];
                $title = sprintf($titleTemplate, $subcategory->name);

                $createdAt = (clone $now)->subDays(random_int(1, 60));
                $resolvedAt = in_array($status, ['resuelto', 'cerrado'])
                    ? (clone $createdAt)->addHours(random_int(2, 72))
                    : null;
                $closedAt = $status === 'cerrado'
                    ? (clone $resolvedAt)->addHours(random_int(1, 24))
                    : null;

                // Assign ~60% of tickets, leave ~40% unassigned
                $assignedTo = random_int(1, 100) <= 60
                    ? (random_int(1, 100) <= 50 ? $agent?->id : $admin?->id)
                    : null;

                $ticket = Ticket::create([
                    'ticket_number' => 'TCK-' . $createdAt->format('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                    'title' => $title,
                    'description' => $description,
                    'user_id' => $deptUser->id,
                    'department_id' => $dept->id,
                    'category_id' => $subcategory->category_id,
                    'subcategory_id' => $subcategory->id,
                    'priority' => $priority,
                    'status' => $status,
                    'assigned_to' => $assignedTo,
                    'resolved_at' => $resolvedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $deptUser->id,
                    'action' => 'creado',
                    'description' => 'Ticket creado por ' . $deptUser->name,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($assignedTo) {
                    TicketHistory::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $assignedTo,
                        'action' => 'asignado',
                        'description' => 'Asignado a ' . ($assignedTo === $agent?->id ? $agent->name : $admin?->name),
                        'created_at' => (clone $createdAt)->addMinutes(random_int(5, 120)),
                        'updated_at' => (clone $createdAt)->addMinutes(random_int(5, 120)),
                    ]);
                }
            }
        }
    }
}
