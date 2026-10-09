<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckSla extends Command
{
    protected $signature = 'sla:check';

    protected $description = 'Warn agents about tickets nearing their SLA and escalate breached ones to admins';

    public function handle(): int
    {
        $admins = User::where('role', 'super_admin')->get();
        $warned = 0;
        $escalated = 0;

        $tickets = Ticket::with('agent')
            ->whereIn('status', ['open', 'in_progress'])
            ->where('escalation_level', '<', 2)
            ->get();

        foreach ($tickets as $ticket) {
            // 1. Deadline passed: escalate to admins and tell the agent
            if ($ticket->due_at->isPast()) {
                $ticket->update(['escalation_level' => 2]);
                Notification::send($admins, new SlaAlert($ticket, 'breach'));
                $ticket->agent?->notify(new SlaAlert($ticket, 'breach'));
                $escalated++;
                continue;
            }

            // 2. 80% of the time used: warn the agent (or admins if unassigned)
            $total = $ticket->created_at->diffInSeconds($ticket->due_at);
            $elapsed = $ticket->created_at->diffInSeconds(now());

            if ($ticket->escalation_level < 1 && $total > 0 && $elapsed >= $total * config('sla.warn_at')) {
                $ticket->update(['escalation_level' => 1]);
                $recipients = $ticket->agent ? collect([$ticket->agent]) : $admins;
                Notification::send($recipients, new SlaAlert($ticket, 'warning'));
                $warned++;
            }
        }

        $this->info("Warned: {$warned}, escalated: {$escalated}");

        return self::SUCCESS;
    }
}