<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AuthTestCase;

class RoleAccessTest extends AuthTestCase
{
    public static function protectedPages(): array
    {
        return [['/user/dashboard'], ['/user/tickets'], ['/user/tickets/create'], ['/user/chat'], ['/agent/dashboard'], ['/admin/dashboard']];
    }

    #[DataProvider('protectedPages')]
    public function test_guests_are_redirected_to_login(string $path): void
    {
        $this->get($path)->assertRedirect('/login');
        $this->getJson($path)->assertUnauthorized();
    }

    public static function roleMatrix(): array
    {
        $cases = [];
        foreach (Role::cases() as $role) {
            foreach (self::protectedPages() as [$path]) {
                $cases[$role->value.' '.$path] = [$role->value, $path, str_starts_with($path, '/'.$role->value.'/')];
            }
        }

        return $cases;
    }

    #[DataProvider('roleMatrix')]
    public function test_role_access_matrix(string $role, string $path, bool $allowed): void
    {
        $account = $this->account(Role::from($role));
        $response = $this->actingAs($account)->get($path);
        if ($allowed) {
            $response->assertOk()->assertSee(e($account->name), false)->assertHeader('Cache-Control', 'no-store, private');
            $response->assertSee('action="'.route('logout').'"', false);
        } else {
            $response->assertForbidden()->assertSee('Akses tidak diizinkan');
        }
    }

    public function test_invalid_role_fails_closed(): void
    {
        $user = $this->account();
        // Deliberately invalid in-memory identity, without weakening the DB constraint.
        $user->role = 'superadmin';
        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
        $this->get('/user/dashboard')->assertForbidden();
        $this->get('/dashboard')->assertForbidden();
    }

    public function test_plain_user_cannot_access_admin_using_headers_or_parameters(): void
    {
        $this->actingAs($this->account())->withHeader('X-Role', 'admin')
            ->get('/admin/dashboard?role=admin&is_admin=1')->assertForbidden();
    }

    public function test_role_change_is_seen_on_the_next_authenticated_request(): void
    {
        $user = $this->account(Role::Admin);
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertOk();
        $user->role = Role::User->value;
        $user->save();
        $this->app['auth']->forgetGuards();
        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_all_user_pages_use_current_identity_without_exposing_passwords(): void
    {
        $user = $this->account();
        foreach (['/user/dashboard', '/user/tickets', '/user/tickets/create', '/user/chat'] as $path) {
            $response = $this->actingAs($user)->get($path)->assertOk();
            $response->assertViewHas('user', fn ($identity) => $identity['id'] === $user->id && $identity['name'] === $user->name);
            $response->assertDontSee($user->password, false)->assertDontSee($user->remember_token, false);
        }
    }
}
