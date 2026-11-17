<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Http\Requests\Tickets\TicketInput;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketMessage;
use App\Models\TicketStatusHistory;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TicketService
{
    public function save(User $actor, array $data, array $files, ?Ticket $existing = null): Ticket
    {
        $stored = [];
        try {
            return DB::transaction(function () use ($actor, $data, $files, $existing, &$stored) {
                $ticket = $existing ? Ticket::lockForUpdate()->findOrFail($existing->id) : new Ticket;
                Gate::forUser($actor)->authorize($existing ? 'update' : 'create', $existing ? $ticket : Ticket::class);
                $ticket->fill(Arr::only($data, ['title', 'description', 'category_id', 'priority', 'department', 'location']));
                $ticket->title = $ticket->title ?: 'Draft tanpa judul';
                $ticket->description = $ticket->description ?? '';
                if (! $existing) {
                    $ticket->user_id = $actor->id;
                    $ticket->ticket_number = 'TK-'.Str::ulid();
                    $ticket->status = TicketStatus::Draft;
                }
                if ($ticket->attachments()->count() + count($files) > 5) {
                    throw ValidationException::withMessages(['attachments' => 'Maksimal 5 lampiran pada draft.']);
                }
                $previous = $existing ? $ticket->status : null;
                $ticket->status = $data['intent'] === 'submit' ? TicketStatus::Open : TicketStatus::Draft;
                $ticket->save();
                if ($previous !== $ticket->status) {
                    $this->history($ticket, $actor, $previous);
                }
                $this->storeFiles($ticket, $actor, $files, $stored);

                return $ticket;
            });
        } catch (Throwable $exception) {
            Storage::disk('ticket_private')->delete($stored);
            throw $exception;
        }
    }

    public function submit(User $actor, Ticket $ticket): Ticket
    {
        return DB::transaction(function () use ($actor, $ticket) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::forUser($actor)->authorize('submit', $ticket);
            Validator::make($ticket->getAttributes(), TicketInput::fields(false))->validate();
            $this->transition($ticket, $actor, TicketStatus::Open);

            return $ticket;
        });
    }

    public function changeStatus(User $actor, Ticket $ticket, TicketStatus $next, ?string $note): void
    {
        DB::transaction(function () use ($actor, $ticket, $next, $note) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::forUser($actor)->authorize('changeStatus', $ticket);
            if (! in_array($next, $ticket->status->next(), true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak sesuai alur penanganan.']);
            }
            $this->transition($ticket, $actor, $next, $note);
        });
    }

    public function assign(User $actor, Ticket $ticket, ?int $assignee): void
    {
        DB::transaction(function () use ($actor, $ticket, $assignee) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::forUser($actor)->authorize('assign', $ticket);
            if ($assignee !== null) {
                $agent = User::lockForUpdate()->find($assignee);
                if (! $agent || $agent->role !== 'agent') {
                    throw ValidationException::withMessages(['assigned_to' => 'Pilih akun dengan role agent.']);
                }
            }
            $ticket->assigned_to = $assignee;
            $ticket->save();
        });
    }

    public function message(User $actor, Ticket $ticket, ?string $body, array $files): void
    {
        $stored = [];
        try {
            DB::transaction(function () use ($actor, $ticket, $body, $files, &$stored) {
                $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
                Gate::forUser($actor)->authorize('message', $ticket);
                $message = new TicketMessage;
                $message->user_id = $actor->id;
                $message->body = $body;
                $ticket->messages()->save($message);
                $this->storeFiles($ticket, $actor, $files, $stored, $message);
                $ticket->touch();
            });
        } catch (Throwable $exception) {
            Storage::disk('ticket_private')->delete($stored);
            throw $exception;
        }
    }

    public function delete(User $actor, Ticket $ticket): void
    {
        $paths = DB::transaction(function () use ($actor, $ticket) {
            $ticket = Ticket::lockForUpdate()->findOrFail($ticket->id);
            Gate::forUser($actor)->authorize('delete', $ticket);
            $paths = $ticket->attachments()->pluck('path')->all();
            $ticket->delete();

            return $paths;
        });
        Storage::disk('ticket_private')->delete($paths);
    }

    private function transition(Ticket $ticket, User $actor, TicketStatus $next, ?string $note = null): void
    {
        $previous = $ticket->status;
        $ticket->status = $next;
        if ($next === TicketStatus::Resolved) {
            $ticket->resolved_at = now();
        } elseif ($next !== TicketStatus::Closed) {
            $ticket->resolved_at = null;
        }
        $ticket->save();
        $this->history($ticket, $actor, $previous, $note);
    }

    private function history(Ticket $ticket, User $actor, ?TicketStatus $previous, ?string $note = null): void
    {
        $history = new TicketStatusHistory;
        $history->user_id = $actor->id;
        $history->from_status = $previous?->value;
        $history->to_status = $ticket->status->value;
        $history->note = $note;
        $ticket->statusHistories()->save($history);
    }

    private function storeFiles(Ticket $ticket, User $actor, array $files, array &$stored, ?TicketMessage $message = null): void
    {
        foreach ($files as $file) {
            $path = $file->store((string) $ticket->id, 'ticket_private');
            $stored[] = $path;
            $attachment = new TicketAttachment;
            $attachment->user_id = $actor->id;
            $attachment->ticket_message_id = $message?->id;
            $attachment->path = $path;
            $attachment->original_name = Str::limit(preg_replace('/[\x00-\x1F\x7F\\\\\/]/u', '_', $file->getClientOriginalName()), 240, '');
            $attachment->mime_type = $file->getMimeType();
            $attachment->size = $file->getSize();
            $ticket->attachments()->save($attachment);
        }
    }
}
