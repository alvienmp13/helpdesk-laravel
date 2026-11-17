<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => Str::lower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:254', Rule::unique(User::class)],
            'password' => PasswordPolicy::rules(),
            'role' => ['prohibited'],
            'is_admin' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'email.unique' => 'Alamat email ini sudah terdaftar. Silakan masuk.',
            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'role.prohibited' => 'Role tidak dapat ditentukan melalui registrasi publik.',
            'is_admin.prohibited' => 'Hak administrator tidak dapat diminta melalui registrasi publik.',
        ];
    }
}
