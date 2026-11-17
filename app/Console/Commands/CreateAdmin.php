<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use App\Support\PasswordPolicy;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateAdmin extends Command
{
    protected $signature = 'helpdesk:create-admin';

    protected $description = 'Membuat akun admin melalui terminal dengan password tersembunyi, tanpa mengubah akun lama.';

    public function handle(): int
    {
        if (! $this->input->isInteractive()) {
            $this->error('Jalankan command ini secara interaktif agar password tidak masuk ke argumen atau riwayat terminal.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama lengkap admin'));
        $email = Str::lower(trim((string) $this->ask('Email admin')));
        $identity = Validator::make(compact('name', 'email'), [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:254', Rule::unique(User::class)],
        ], ['email.unique' => 'Email sudah terdaftar. Command ini tidak mengubah role atau password akun yang ada.']);

        if ($identity->fails()) {
            foreach ($identity->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->line('Password minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka; maksimal 72 byte.');
        // No --password option and no visible fallback if the terminal cannot hide input.
        $password = $this->secret('Password admin', false);
        $confirmation = $this->secret('Konfirmasi password admin', false);
        $validation = Validator::make(['password' => $password, 'password_confirmation' => $confirmation], ['password' => PasswordPolicy::rules()]);
        if ($validation->fails()) {
            foreach ($validation->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (! $this->confirm("Buat admin baru untuk {$email}?", false)) {
            $this->info('Pembuatan admin dibatalkan.');

            return self::SUCCESS;
        }

        $user = new User(compact('name', 'email'));
        $user->password = Hash::make($password);
        $user->role = Role::Admin->value;
        try {
            $user->save();
        } catch (UniqueConstraintViolationException) {
            $this->error('Email sudah terdaftar. Tidak ada akun lama yang diubah.');

            return self::FAILURE;
        }

        $this->info('Akun admin berhasil dibuat. Masuk melalui /login.');

        return self::SUCCESS;
    }
}
