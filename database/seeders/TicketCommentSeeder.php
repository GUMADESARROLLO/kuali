<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketCommentSeeder extends Seeder
{
    public function run(): void
    {
        $agent = User::where('email', 'agente@kuali.test')->first();
        $admin = User::where('email', 'admin@kuali.test')->first();

        $tickets = Ticket::whereIn('status', ['en_proceso', 'en_espera', 'resuelto', 'abierto'])
            ->inRandomOrder()
            ->limit(12)
            ->get();

        $agentReplies = [
            'Gracias por reportarlo. Ya estamos revisando el incidente con el equipo de desarrollo.',
            'Hemos identificado el problema. Se debe a un conflicto en la ultima actualizacion. Estamos trabajando en una solucion.',
            '¿Podria proporcionarnos mas detalles sobre cuando ocurre este error? Especificamente, ¿que pasos realiza antes de que aparezca?',
            'Hemos aplicado una correccion temporal mientras se despliega el parche definitivo. Por favor, confirme si el problema persiste.',
            'Estimado usuario, su solicitud ha sido escalada al equipo de infraestructura. Le mantendremos informado.',
            'El problema reportado ya ha sido solucionado en nuestro ultimo despliegue. ¿Podria confirmar que todo funciona correctamente?',
            'Para poder ayudarle mejor, ¿podria enviarnos una captura de pantalla del error que aparece?',
            'Hemos revisado los logs del servidor y no encontramos anomalias. ¿Podria intentar nuevamente desde otro dispositivo?',
        ];

        $userFollowUps = [
            'Gracias por la pronta respuesta. Voy a probar la solucion que me indicaron y les confirmo.',
            'El problema continua. Ya intente los pasos que me recomendaron pero el error persiste.',
            'Adjunto la captura de pantalla solicitada. Espero que ayude a diagnosticar el problema.',
            'Muchas gracias por la ayuda. El problema se ha resuelto exitosamente.',
            '¿Hay alguna actualizacion sobre el estado de mi solicitud? Ya pasaron varios dias.',
        ];

        foreach ($tickets as $ticket) {
            $createdAt = (clone $ticket->created_at)->addHours(random_int(2, 8));

            // Agent reply
            if ($agent || $admin) {
                $a = random_int(1, 100) <= 60 ? $agent : $admin;
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $a->id,
                    'comment' => $agentReplies[array_rand($agentReplies)],
                    'is_internal' => false,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Sometimes add an internal note
                if (random_int(1, 100) <= 35) {
                    TicketComment::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $a->id,
                        'comment' => 'Revisado con DevOps. El issue esta siendo investigado. Posible causa: configuracion de servidor.',
                        'is_internal' => true,
                        'created_at' => (clone $createdAt)->addMinutes(random_int(5, 30)),
                        'updated_at' => (clone $createdAt)->addMinutes(random_int(5, 30)),
                    ]);
                }
            }

            // User follow-up on ~40% of tickets
            if (random_int(1, 100) <= 40) {
                $userReplyAt = (clone $createdAt)->addHours(random_int(2, 24));
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $ticket->user_id,
                    'comment' => $userFollowUps[array_rand($userFollowUps)],
                    'is_internal' => false,
                    'created_at' => $userReplyAt,
                    'updated_at' => $userReplyAt,
                ]);
            }
        }
    }
}
