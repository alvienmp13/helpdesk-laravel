<?php

namespace App\Support;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Str;

class TicketPresenter
{
    public static function card(Ticket $ticket): array
    {
        $status = $ticket->status;
        $completed = in_array($status, [TicketStatus::Resolved, TicketStatus::Closed], true);

        return [
            'id' => $ticket->ticket_number, 'key' => $ticket->id, 'url' => route('tickets.show', $ticket),
            'title' => $ticket->title, 'description' => $ticket->description,
            'category' => $ticket->category?->name ?? 'Belum dipilih',
            'detail_category' => $ticket->category?->name ?? 'Belum dipilih',
            'icon' => $ticket->category?->icon ?? 'ticket',
            'status' => $status->label(), 'status_value' => $status->value,
            'state' => $completed ? 'completed' : ($status === TicketStatus::WaitingUser ? 'waiting' : ($status === TicketStatus::Draft ? 'draft' : 'active')),
            'tone' => $completed ? 'mint' : ($status === TicketStatus::WaitingUser ? 'amber' : 'blue'),
            'priority' => $ticket->priority,
            'priority_label' => 'Prioritas '.(['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'urgent' => 'Mendesak'][$ticket->priority]),
            'time' => 'Dibuat: '.$ticket->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB',
            'submitted' => $ticket->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i').' WIB',
            'technician' => $ticket->assignee?->name ?? 'Belum ditugaskan',
            'initials' => $ticket->assignee ? Str::upper(Str::substr($ticket->assignee->name, 0, 2)) : '—',
            'team' => 'IT Support', 'role' => 'IT Support', 'technician_role' => 'IT Support',
            'department' => $ticket->department, 'location' => $ticket->location,
        ];
    }

    public static function detail(Ticket $ticket, User $viewer): array
    {
        return [
            ...self::card($ticket), 'model' => $ticket,
            'timeline' => $ticket->statusHistories->map(fn ($history) => [
                'title' => ($history->from_status ? TicketStatus::from($history->from_status)->label().' → ' : '').TicketStatus::from($history->to_status)->label(),
                'detail' => $history->created_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB · '.($history->user?->name ?? 'Akun dihapus').($history->note ? ' · '.$history->note : ''),
                'state' => 'done',
            ])->all(),
            'messages' => $ticket->messages->map(fn ($message) => [
                'author' => $message->user?->name ?? 'Akun dihapus',
                'time' => $message->created_at->timezone('Asia/Jakarta')->format('d M Y H:i').' WIB',
                'own' => $message->user_id === $viewer->id, 'body' => $message->body,
                'attachments' => $message->attachments,
            ])->all(),
            'attachments' => $ticket->attachments->whereNull('ticket_message_id'),
        ];
    }
}
