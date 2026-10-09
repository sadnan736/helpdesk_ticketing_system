<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    // Admin sees all, agent sees assigned, customer sees own
    public function view(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin()
            || ($user->isAgent() && $ticket->agent_id === $user->id)
            || ($user->isCustomer() && $ticket->customer_id === $user->id);
    }

    // Anyone who can view a ticket can reply to it
    public function comment(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket);
    }

    // Change status or assignment: admins and the assigned agent only
    public function manage(User $user, Ticket $ticket): bool
    {
        return $user->isAdmin()
            || ($user->isAgent() && $ticket->agent_id === $user->id);
    }
}