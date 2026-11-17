<?php

namespace App\Http\Controllers\User;

use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tickets\SaveTicketRequest;
use App\Http\Requests\Tickets\SubmitDraftRequest;
use App\Http\Requests\Tickets\TicketIndexRequest;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use App\Support\TicketPresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    public function __invoke(TicketIndexRequest $request): View { return $this->listing($request); }

    public function show(TicketIndexRequest $request, Ticket $ticket): View
    {
        Gate::authorize('view', $ticket);
        return $this->listing($request, $ticket);
    }

    private function listing(TicketIndexRequest $request, ?Ticket $selected = null): View
    {
        $actor = $request->user();
        $base = Ticket::visibleTo($actor);
        $byStatus = (clone $base)->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts = [
            'all' => $byStatus->sum(), 'draft' => $byStatus->get('draft', 0),
            'active' => $byStatus->get('open', 0) + $byStatus->get('in_progress', 0) + $byStatus->get('waiting_user', 0),
            'waiting' => $byStatus->get('waiting_user', 0),
            'completed' => $byStatus->get('resolved', 0) + $byStatus->get('closed', 0),
        ];
        $filters = $request->validated();
        $status = $filters['status'] ?? 'all';
        $statuses = match ($status) {
            'all' => [], 'active' => ['open', 'in_progress', 'waiting_user'],
            'waiting' => ['waiting_user'], 'completed' => ['resolved', 'closed'], default => [$status],
        };
        $query = (clone $base)->with(['category', 'assignee']);
        if ($statuses) $query->whereIn('status', $statuses);
        if (($filters['priority'] ?? 'all') !== 'all' && ! empty($filters['priority'])) $query->where('priority', $filters['priority']);
        if (! empty($filters['q'])) {
            // Escape literal wildcard characters without relying on MySQL SQL mode.
            $search = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['q']).'%';
            $query->where(fn ($q) => $q->whereRaw("ticket_number LIKE ? ESCAPE '!'", [$search])->orWhereRaw("title LIKE ? ESCAPE '!'", [$search]));
        }
        $page = $query->latest('id')->paginate(10)->withQueryString();
        $selected ??= $page->first();
        if ($selected) {
            Gate::authorize('view', $selected);
            $selected->load(['category', 'assignee', 'messages.user', 'messages.attachments', 'attachments', 'statusHistories.user']);
        }
        return view('user.tickets', [
            'user' => $actor->portalIdentity(), 'tickets' => $page->getCollection()->map(TicketPresenter::card(...))->all(),
            'pagination' => $page, 'counts' => $counts, 'filters' => $filters,
            'selectedTicket' => $selected ? TicketPresenter::detail($selected, $actor) : null,
            'statuses' => TicketStatus::cases(),
            'agents' => $actor->role === 'admin' ? User::where('role', 'agent')->orderBy('name')->get(['id', 'name']) : collect(),
            'listUrl' => route($actor->role === 'user' ? 'user.tickets' : 'support.tickets'),
        ]);
    }

    public function store(SaveTicketRequest $request, TicketService $service): RedirectResponse
    {
        $ticket = $service->save($request->user(), $request->validated(), $request->file('attachments', []));
        return to_route('tickets.show', $ticket)->with('success', $ticket->status === TicketStatus::Draft ? 'Draft tersimpan.' : 'Tiket berhasil dikirim.');
    }

    public function update(SaveTicketRequest $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        $ticket = $service->save($request->user(), $request->validated(), $request->file('attachments', []), $ticket);
        return to_route('tickets.show', $ticket)->with('success', $ticket->status === TicketStatus::Draft ? 'Draft diperbarui.' : 'Tiket berhasil dikirim.');
    }

    public function submit(SubmitDraftRequest $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        $service->submit($request->user(), $ticket);
        return to_route('tickets.show', $ticket)->with('success', 'Draft berhasil dikirim.');
    }

    public function destroy(Request $request, Ticket $ticket, TicketService $service): RedirectResponse
    {
        Gate::authorize('delete', $ticket);
        $service->delete($request->user(), $ticket);
        return to_route('user.tickets')->with('success', 'Draft dihapus.');
    }
}
