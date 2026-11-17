<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AuthTestCase;

class RegistrationTest extends AuthTestCase
{
    public function test_guest_can_open_registration_without_any_role_selector(): void
    {
        $this->get('/register')->assertOk()->assertSee('Buat akun HelpDesk')
            ->assertSee('name="_token"', false)->assertDontSee('name="role"', false)->assertDontSee('name="is_admin"', false);
    }

    public function test_registration_creates_a_hashed_user_account_and_rotates_session(): void
    {
        $this->withSession(['url.intended' => '/admin/dashboard']);
        $oldSession = session()->getId();
        $oldToken = session()->token();
        $this->post('/register', $this->registration(['email' => '  Ayu@Example.Test  ']))->assertRedirect('/user/dashboard')->assertSessionHasNoErrors();
        $user = User::sole();
        $this->assertSame('user', $user->role);
        $this->assertSame('ayu@example.test', $user->email);
        $this->assertNotSame(self::PASSWORD, $user->password);
        $this->assertTrue(Hash::check(self::PASSWORD, $user->password));
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSession, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->assertFalse(session()->has('url.intended'));
    }

    public static function rolePayloads(): array
    {
        return [['role', 'admin'], ['role', 'agent'], ['role', 'user'], ['role', ['admin']], ['is_admin', true]];
    }

    public function test_password_that_looks_like_a_hash_is_hashed_as_new_plaintext(): void
    {
        $input = '$2y$04$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $this->post('/register', $this->registration(['password' => $input, 'password_confirmation' => $input]))
            ->assertRedirect('/user/dashboard');
        $stored = User::sole()->password;
        $this->assertNotSame($input, $stored);
        $this->assertTrue(Hash::check($input, $stored));
    }

    #[DataProvider('rolePayloads')]
    public function test_public_registration_rejects_role_injection(string $field, mixed $value): void
    {
        $this->post('/register', $this->registration([$field => $value]))->assertSessionHasErrors($field);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_query_string_cannot_assign_admin_role(): void
    {
        $this->post('/register?role=admin', $this->registration())->assertSessionHasErrors('role');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_unrelated_privilege_fields_are_not_mass_assigned(): void
    {
        $this->post('/register', $this->registration(['permissions' => ['*'], 'email_verified_at' => now(), 'id' => 999]))->assertRedirect('/user/dashboard');
        $user = User::sole();
        $this->assertSame('user', $user->role);
        $this->assertNull($user->email_verified_at);
        $this->assertNotSame(999, $user->id);
    }

    public static function invalidForms(): array
    {
        return [
            'missing name' => [['name' => ''], 'name'],
            'long name' => [['name' => str_repeat('a', 101)], 'name'],
            'invalid email' => [['email' => 'not-an-email'], 'email'],
            'array email' => [['email' => ['x']], 'email'],
            'weak password' => [['password' => 'password', 'password_confirmation' => 'password'], 'password'],
            'mismatch' => [['password_confirmation' => 'DifferentPassword123'], 'password'],
            'bcrypt byte limit' => [['password' => str_repeat('é', 36).'Aa1', 'password_confirmation' => str_repeat('é', 36).'Aa1'], 'password'],
        ];
    }

    #[DataProvider('invalidForms')]
    public function test_registration_validates_fields(array $values, string $field): void
    {
        $this->post('/register', $this->registration($values))->assertSessionHasErrors($field);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertFalse(session()->has('_old_input.password'));
        $this->assertFalse(session()->has('_old_input.password_confirmation'));
    }

    public function test_duplicate_email_does_not_overwrite_an_existing_account(): void
    {
        $user = User::factory()->admin()->create(['email' => 'ayu@example.test']);
        $password = $user->password;
        $this->post('/register', $this->registration(['email' => 'AYU@EXAMPLE.TEST']))->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($password, $user->fresh()->password);
        $this->assertSame('admin', $user->fresh()->role);
        $this->assertGuest();
    }

    public function test_role_cannot_be_mass_assigned_on_existing_or_new_users(): void
    {
        $user = $this->account();
        $user->fill(['role' => 'admin', 'name' => 'Updated Name'])->save();
        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame('user', (new User(['role' => 'admin']))->role);
    }

    public function test_registration_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', [])->assertSessionHasErrors();
        }
        $this->post('/register', $this->registration())->assertStatus(429);
        $this->assertDatabaseCount('users', 0);
    }
}
