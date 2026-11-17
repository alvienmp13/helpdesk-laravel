<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesIsolatedDatabase;
use Tests\TestCase;

class RoleMigrationTest extends TestCase
{
    use UsesIsolatedDatabase;

    public function test_role_migration_preserves_existing_user_data_and_defaults_to_user(): void
    {
        $this->assertIsolatedDatabase();
        $this->artisan('migrate', ['--path' => 'database/migrations/0001_01_01_000000_create_users_table.php', '--force' => true])->assertSuccessful();
        $before = ['name' => 'Existing Employee', 'email' => 'existing@example.test', 'password' => Hash::make('ExistingPassword123')];
        $id = DB::table('users')->insertGetId($before);
        $this->assertFalse(Schema::hasColumn('users', 'role'));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseHas('users', $before + ['id' => $id, 'role' => 'user']);
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Schema::hasIndex('users', ['role']));
    }
}
