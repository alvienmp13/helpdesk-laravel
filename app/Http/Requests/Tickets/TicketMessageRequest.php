<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class TicketMessageRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('message', $this->route('ticket')); }

    public function rules(): array
    {
        return [
            'body' => ['required_without:attachments', 'nullable', 'string', 'max:2000'],
            ...TicketInput::attachments(),
            ...TicketInput::protectedFields(),
            'ticket_id' => ['prohibited'], 'ticket_message_id' => ['prohibited'],
        ];
    }
}
