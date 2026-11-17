<?php

namespace App\Policies;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool { return in_array($user->role, ['user', 'agent', 'admin'], true); }
    public function create(User $user): bool { return $user->role === 'user'; }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->role === 'admin'
            || ($user->role === 'user' && $ticket->user_id === $user->id)
            || ($user->role === 'agent' && $ticket->assigned_to === $user->id && $ticket->status !== TicketStatus::Draft);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->role === 'user' && $ticket->user_id === $user->id && $ticket->status === TicketStatus::Draft;
    }

    public function delete(User $user, Ticket $ticket): bool { return $this->update($user, $ticket); }
    public function submit(User $user, Ticket $ticket): bool { return $this->update($user, $ticket); }

    public function changeStatus(User $user, Ticket $ticket): bool
    {
        return in_array($user->role, ['admin', 'agent'], true) && $this->view($user, $ticket)
            && !in_array($ticket->status, [TicketStatus::Draft, TicketStatus::Closed], true);
    }

    public function assign(User $user, Ticket $ticket): bool
    {
        return $user->role === 'admin' && !in_array($ticket->status, [TicketStatus::Draft, TicketStatus::Closed], true);
    }

    public function message(User $user, Ticket $ticket): bool
    {
        return $this->view($user, $ticket) && !in_array($ticket->status, [TicketStatus::Draft, TicketStatus::Closed], true);
    }

    public function download(User $user, Ticket $ticket): bool { return $this->view($user, $ticket); }
}
