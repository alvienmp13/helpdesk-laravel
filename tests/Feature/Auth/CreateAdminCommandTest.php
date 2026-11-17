<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\AuthTestCase;

class CreateAdminCommandTest extends AuthTestCase
{
    public function test_command_creates_admin_with_hidden_password_and_confirmation(): void
    {
        $this->artisan('helpdesk:create-admin')->expectsQuestion('Nama lengkap admin', 'Admin HelpDesk')
            ->expectsQuestion('Email admin', 'ADMIN@example.test')
            ->expectsQuestion('Password admin', self::PASSWORD)
            ->expectsQuestion('Konfirmasi password admin', self::PASSWORD)
            ->expectsConfirmation('Buat admin baru untuk admin@example.test?', 'yes')->assertSuccessful();
        $user = User::sole();
        $this->assertSame('admin', $user->role);
        $this->assertSame('admin@example.test', $user->email);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
    }

    public function test_command_does_not_promote_or_replace_an_existing_user(): void
    {
        $user = $this->account();
        $before = $user->fresh()->getAttributes();
        $this->artisan('helpdesk:create-admin')->expectsQuestion('Nama lengkap admin', 'Admin HelpDesk')
            ->expectsQuestion('Email admin', $user->email)->assertFailed();
        $this->assertSame($before, $user->fresh()->getAttributes());
        $this->assertDatabaseCount('users', 1);
    }

    public function test_command_rejects_weak_password(): void
    {
        $this->artisan('helpdesk:create-admin')->expectsQuestion('Nama lengkap admin', 'Admin HelpDesk')
            ->expectsQuestion('Email admin', 'admin@example.test')
            ->expectsQuestion('Password admin', 'weak')->expectsQuestion('Konfirmasi password admin', 'weak')->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_command_can_be_cancelled_without_creating_account(): void
    {
        $this->artisan('helpdesk:create-admin')->expectsQuestion('Nama lengkap admin', 'Admin HelpDesk')
            ->expectsQuestion('Email admin', 'admin@example.test')
            ->expectsQuestion('Password admin', self::PASSWORD)->expectsQuestion('Konfirmasi password admin', self::PASSWORD)
            ->expectsConfirmation('Buat admin baru untuk admin@example.test?', 'no')->assertSuccessful();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_noninteractive_command_fails_without_default_credentials(): void
    {
        $this->artisan('helpdesk:create-admin', ['--no-interaction' => true])->assertFailed();
        $this->assertDatabaseCount('users', 0);
    }
}
