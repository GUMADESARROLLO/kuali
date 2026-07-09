<?php

namespace App\Console\Commands;

use App\Mail\TicketNotification;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckSlaCommand extends Command
{
    protected $signature = 'sla:check';
    protected $description = 'Check SLA deadlines and mark escalated tickets';

    public function handle(): int
    {
        $now = Carbon::now();

        // Tickets where solution deadline passed
        Ticket::whereIn('status', ['abierto', 'en_proceso'])
            ->where('sla_rule_id', '!=', null)
            ->where('solution_due_at', '<', $now)
            ->where('is_escalated', false)
            ->chunk(50, function ($tickets) use ($now) {
                foreach ($tickets as $ticket) {
                    $ticket->update([
                        'is_escalated' => true,
                        'escalated_at' => $now,
                    ]);

                    if ($ticket->assignedAgent) {
                        try {
                            Mail::to($ticket->assignedAgent->email)
                                ->send(new TicketNotification($ticket, 'resolved'));
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                }
            });

        // Tickets approaching escalation (15 min before solution_due)
        $warningWindow = (clone $now)->addMinutes(15);
        Ticket::whereIn('status', ['abierto', 'en_proceso'])
            ->where('sla_rule_id', '!=', null)
            ->where('solution_due_at', '>', $now)
            ->where('solution_due_at', '<', $warningWindow)
            ->where('is_escalated', false)
            ->chunk(50, function ($tickets) {
                foreach ($tickets as $ticket) {
                    if ($ticket->assignedAgent) {
                        try {
                            Mail::to($ticket->assignedAgent->email)
                                ->send(new TicketNotification($ticket, 'resolved'));
                        } catch (\Throwable $e) {
                            report($e);
                        }
                    }
                }
            });

        $this->info('SLA check completed.');
        return Command::SUCCESS;
    }
}
