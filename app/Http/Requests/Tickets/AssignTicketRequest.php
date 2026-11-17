<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('assign', $this->route('ticket')); }

    public function rules(): array
    {
        return [
            ...TicketInput::protectedFields(),
            'assigned_to' => ['present', 'nullable', 'integer', Rule::exists('users', 'id')->where('role', 'agent')],
        ];
    }
}
