<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\Tickets\AssignTicketRequest;
use App\Http\Requests\Tickets\TicketMessageRequest;
use App\Http\Requests\Tickets\TicketStatusRequest;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Services\TicketService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketActivityController extends Controller
{
    public function message(TicketMessageRequest $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        $service->message($request->user(), $ticket, $request->validated('body'), $request->file('attachments', []));

        return to_route('tickets.show', $ticket)->with('success', 'Pesan terkirim.');
    }

    public function status(TicketStatusRequest $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        $service->changeStatus($request->user(), $ticket, TicketStatus::from($request->validated('status')), $request->validated('note'));

        return to_route('tickets.show', $ticket)->with('success', 'Status tiket diperbarui.');
    }

    public function assign(AssignTicketRequest $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        $service->assign($request->user(), $ticket, $request->validated('assigned_to'));

        return to_route('tickets.show', $ticket)->with('success', 'Penugasan diperbarui.');
    }

    public function download(TicketAttachment $attachment): StreamedResponse
    {
        Gate::authorize('download', $attachment->ticket);
        $disk = Storage::disk('ticket_private');
        abort_unless($disk->exists($attachment->path), 404);

        return $disk->download($attachment->path, $attachment->original_name, [
            'Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
