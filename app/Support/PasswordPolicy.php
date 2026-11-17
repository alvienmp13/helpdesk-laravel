<?php

namespace App\Support;

use Closure;
use Illuminate\Validation\Rules\Password;

class PasswordPolicy
{
    public static function rules(): array
    {
        return [
            'required', 'string', 'confirmed',
            Password::min(12)->mixedCase()->numbers(),
            // Bcrypt accepts at most 72 bytes, which differs from character count for Unicode.
            function (string $attribute, mixed $value, Closure $fail): void {
                if (is_string($value) && strlen($value) > 72) {
                    $fail('Password maksimal 72 byte. Gunakan password yang lebih pendek.');
                }
            },
        ];
    }
}
