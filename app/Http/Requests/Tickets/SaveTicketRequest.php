<?php

namespace App\Http\Requests\Tickets;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        $ticket = $this->route('ticket');

        return $ticket instanceof Ticket
            ? $this->user()->can('update', $ticket)
            : $this->user()->can('create', Ticket::class);
    }

    public function rules(): array
    {
        return [
            'intent' => ['required', Rule::in(['draft', 'submit'])],
            ...TicketInput::fields($this->input('intent') === 'draft'),
            ...TicketInput::attachments(),
            ...TicketInput::protectedFields(),
        ];
    }
}
