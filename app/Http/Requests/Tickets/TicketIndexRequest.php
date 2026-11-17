<?php

namespace App\Http\Requests\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketIndexRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('viewAny', Ticket::class); }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in([...array_column(TicketStatus::cases(), 'value'), 'all', 'active', 'waiting', 'completed'])],
            'priority' => ['nullable', Rule::in(['all', 'low', 'medium', 'high', 'urgent'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
