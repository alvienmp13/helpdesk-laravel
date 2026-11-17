<?php

namespace App\Http\Requests\Tickets;

use Illuminate\Validation\Rule;

class TicketInput
{
    public static function fields(bool $draft): array
    {
        return [
            'title' => [$draft ? 'nullable' : 'required', 'string', $draft ? 'min:1' : 'min:5', 'max:150'],
            'description' => [$draft ? 'nullable' : 'required', 'string', $draft ? 'min:1' : 'min:20', 'max:5000'],
            'category_id' => [$draft ? 'nullable' : 'required', 'integer', Rule::exists('categories', 'id')],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'department' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
        ];
    }

    public static function attachments(): array
    {
        return [
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['bail', 'required', 'file', 'min:0.001', 'max:5120',
                'mimetypes:image/png,image/jpeg,application/pdf,text/plain', 'extensions:png,jpg,jpeg,pdf,log',
                function ($attribute, $file, $fail) {
                    $expected = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'pdf' => 'application/pdf', 'log' => 'text/plain'];
                    if (($expected[strtolower($file->getClientOriginalExtension())] ?? null) !== $file->getMimeType()) {
                        $fail('Isi lampiran harus sesuai dengan ekstensi berkas.');
                    }
                }],
        ];
    }

    public static function protectedFields(): array
    {
        return array_fill_keys(['user_id', 'assigned_to', 'status', 'resolved_at', 'ticket_number'], ['prohibited']);
    }
}
