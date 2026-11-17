<?php

namespace Tests;

use App\Enums\Role;
use App\Models\User;
use Tests\Concerns\UsesIsolatedDatabase;

abstract class AuthTestCase extends TestCase
{
    use UsesIsolatedDatabase;

    protected const PASSWORD = 'ValidHelpdesk123!';

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertIsolatedDatabase();
        $this->withoutVite();
        // A new in-memory database per test: additive migrations only, no migrate:fresh.
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
    }

    protected function account(Role $role = Role::User): User
    {
        return User::factory()->create(['role' => $role->value, 'password' => self::PASSWORD]);
    }

    protected function registration(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Ayu HelpDesk',
            'email' => 'ayu@example.test',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ], $overrides);
    }
}
