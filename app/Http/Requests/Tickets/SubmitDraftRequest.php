<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Foundation\Http\FormRequest;

class SubmitDraftRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('submit', $this->route('ticket')); }
    public function rules(): array { return TicketInput::protectedFields(); }
}
