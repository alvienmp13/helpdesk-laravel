<?php

namespace App\Http\Requests\Tickets;

use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketStatusRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('changeStatus', $this->route('ticket')); }

    public function rules(): array
    {
        return [
            ...TicketInput::protectedFields(),
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
