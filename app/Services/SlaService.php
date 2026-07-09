<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\SlaRule;
use App\Models\Ticket;
use Carbon\Carbon;

class SlaService
{
    /**
     * Apply the best matching SLA rule to a ticket and calculate deadlines.
     */
    public function apply(Ticket $ticket): void
    {
        $rule = $this->findRule($ticket);
        if (!$rule || !$rule->calendar) {
            $ticket->update([
                'sla_rule_id' => null,
                'first_response_due_at' => null,
                'update_due_at' => null,
                'solution_due_at' => null,
                'is_escalated' => false,
            ]);
            return;
        }

        $createdAt = Carbon::parse($ticket->created_at);

        $this->recalculate($ticket, $rule, $createdAt);
    }

    /**
     * Recalculate deadlines — used when a comment is added (update time) or
     * when the ticket status changes back to open.
     */
    public function recalculate(Ticket $ticket, ?SlaRule $rule = null, ?Carbon $from = null): void
    {
        $rule ??= $ticket->slaRule;
        if (!$rule || !$rule->calendar) return;

        $from ??= Carbon::now();
        $calendar = $rule->calendar;

        $firstResponseDue = $rule->first_response_hours > 0 && !$ticket->first_response_due_at
            ? $this->addBusinessHours(Carbon::parse($ticket->created_at), $rule->first_response_hours, $calendar)
            : $ticket->first_response_due_at;

        $updateDue = $rule->update_hours > 0
            ? $this->addBusinessHours(clone $from, $rule->update_hours, $calendar)
            : null;

        $solutionDue = $rule->solution_hours > 0 && !$ticket->solution_due_at
            ? $this->addBusinessHours(Carbon::parse($ticket->created_at), $rule->solution_hours, $calendar)
            : $ticket->solution_due_at;

        $ticket->update([
            'sla_rule_id' => $rule->id,
            'first_response_due_at' => $firstResponseDue,
            'update_due_at' => $updateDue,
            'solution_due_at' => $solutionDue,
        ]);
    }

    /**
     * Add N business hours to a start date using the calendar definition.
     */
    public function addBusinessHours(Carbon $start, int $hoursToAdd, Calendar $calendar): Carbon
    {
        $hoursData = $calendar->hours;
        $remaining = $hoursToAdd;
        $current = clone $start;

        // If start is outside business hours, move to next open slot
        $dayKey = strtolower($current->format('D'));
        $dayConfig = $hoursData[$dayKey] ?? null;

        if ($dayConfig && ($dayConfig['enabled'] ?? false)) {
            $dayStart = Carbon::parse($dayConfig['start']);
            $dayEnd = Carbon::parse($dayConfig['end']);
            $open = (clone $current)->setTime((int)$dayStart->format('H'), (int)$dayStart->format('i'));
            $close = (clone $current)->setTime((int)$dayEnd->format('H'), (int)$dayEnd->format('i'));

            if ($current->lt($open)) {
                $current = clone $open;
            } elseif ($current->gte($close)) {
                $current = $this->nextOpenTime($current, $hoursData);
            }
        } else {
            $current = $this->nextOpenTime($current, $hoursData);
        }

        // Accumulate hours day by day
        $maxIterations = 365;
        while ($remaining > 0 && $maxIterations-- > 0) {
            $dayKey = strtolower($current->format('D'));
            $dayConfig = $hoursData[$dayKey] ?? null;

            if (!$dayConfig || !($dayConfig['enabled'] ?? false)) {
                $current->addDay()->startOfDay();
                $current = $this->nextOpenTime($current, $hoursData);
                continue;
            }

            $dayStart = Carbon::parse($dayConfig['start']);
            $dayEnd = Carbon::parse($dayConfig['end']);
            $open = (clone $current)->setTime((int)$dayStart->format('H'), (int)$dayStart->format('i'));
            $close = (clone $current)->setTime((int)$dayEnd->format('H'), (int)$dayEnd->format('i'));

            $availableToday = $current->diffInMinutes($close, false);
            if ($availableToday <= 0) {
                $current->addDay()->startOfDay();
                $current = $this->nextOpenTime($current, $hoursData);
                continue;
            }

            $needed = $remaining * 60;
            if ($needed <= $availableToday) {
                $current->addMinutes($needed);
                $remaining = 0;
            } else {
                $remaining -= $availableToday / 60;
                $current->addDay()->startOfDay();
                $current = $this->nextOpenTime($current, $hoursData);
            }
        }

        return $current;
    }

    /**
     * Find the next open time from a given date.
     */
    private function nextOpenTime(Carbon $from, array $hoursData): Carbon
    {
        $current = clone $from;
        for ($i = 0; $i < 14; $i++) {
            $dayKey = strtolower($current->format('D'));
            $dayConfig = $hoursData[$dayKey] ?? null;
            if ($dayConfig && ($dayConfig['enabled'] ?? false)) {
                $dayStart = Carbon::parse($dayConfig['start']);
                return (clone $current)->setTime((int)$dayStart->format('H'), (int)$dayStart->format('i'));
            }
            $current->addDay()->startOfDay();
        }
        return $from; // fallback
    }

    /**
     * Find the first active SLA rule that matches the ticket conditions.
     */
    public function findRule(Ticket $ticket): ?SlaRule
    {
        return SlaRule::where('is_active', true)
            ->with('calendar')
            ->orderBy('sort_order')
            ->get()
            ->first(fn(SlaRule $rule) => $this->matches($rule, $ticket));
    }

    private function matches(SlaRule $rule, Ticket $ticket): bool
    {
        $conditions = $rule->conditions ?? [];
        if (empty($conditions)) return true;

        if (!empty($conditions['department_id']) && !in_array($ticket->department_id, $conditions['department_id'])) {
            return false;
        }
        if (!empty($conditions['category_id']) && !in_array($ticket->category_id, $conditions['category_id'])) {
            return false;
        }
        if (!empty($conditions['priority']) && !in_array($ticket->priority, $conditions['priority'])) {
            return false;
        }
        return true;
    }
}
