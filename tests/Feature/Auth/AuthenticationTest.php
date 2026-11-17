<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AuthTestCase;

class AuthenticationTest extends AuthTestCase
{
    public static function roles(): array
    {
        return [['user'], ['agent'], ['admin']];
    }

    public function test_login_page_is_available_to_guests(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk ke HelpDesk')->assertSee('name="_token"', false);
    }

    #[DataProvider('roles')]
    public function test_login_redirects_to_role_dashboard_and_rotates_session(string $role): void
    {
        $user = $this->account(Role::from($role));
        $this->withSession(['url.intended' => 'https://example.invalid/admin']);
        $oldSession = session()->getId();
        $oldToken = session()->token();
        $this->post('/login', ['email' => strtoupper($user->email), 'password' => self::PASSWORD, 'role' => 'admin', 'redirect' => '/admin/dashboard'])
            ->assertRedirect('/'.$role.'/dashboard')->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $this->assertSame($role, $user->fresh()->role);
        $this->assertNotSame($oldSession, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->assertFalse(session()->has('url.intended'));
    }

    public function test_invalid_credentials_fail_without_revealing_whether_email_exists(): void
    {
        $user = $this->account();
        foreach ([$user->email, 'missing@example.test'] as $email) {
            $this->post('/login', ['email' => $email, 'password' => 'WrongPassword123'])->assertSessionHasErrors(['email' => 'Email atau password tidak sesuai.']);
            $this->assertGuest();
            $this->assertFalse(session()->has('_old_input.password'));
        }
    }

    public function test_login_requires_valid_email_and_password(): void
    {
        $this->post('/login', ['email' => ['invalid']])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_remember_me_sets_a_recaller_cookie(): void
    {
        $user = $this->account();
        $response = $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD, 'remember' => '1']);
        $response->assertRedirect('/user/dashboard')->assertCookie(auth()->guard()->getRecallerName());
        $this->assertNotEmpty($user->fresh()->remember_token);
    }

    public function test_login_locks_out_after_five_failed_attempts_and_recovers_after_timeout(): void
    {
        $user = $this->account();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'WrongPassword123'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertGuest();
        $this->travel(61)->seconds();
        $this->post('/login', ['email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/user/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('roles')]
    public function test_authenticated_accounts_are_redirected_away_from_auth_forms(string $role): void
    {
        $this->actingAs($this->account(Role::from($role)));
        foreach (['/', '/dashboard', '/login', '/register'] as $path) {
            $this->get($path)->assertRedirect('/'.$role.'/dashboard');
        }
        $this->post('/register', $this->registration())->assertRedirect('/'.$role.'/dashboard');
        $this->assertDatabaseCount('users', 1);
    }

    #[DataProvider('roles')]
    public function test_logout_invalidates_session_and_blocks_reentry(string $role): void
    {
        $this->actingAs($user = $this->account(Role::from($role)))->withSession(['private-marker' => 'secret']);
        $oldSession = session()->getId();
        $oldToken = session()->token();
        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('private-marker');
        $this->assertGuest();
        $this->assertNotSame($oldSession, session()->getId());
        $this->assertNotSame($oldToken, session()->token());
        $this->get('/'.$role.'/dashboard')->assertRedirect('/login');
    }

    public function test_logout_is_not_available_over_get(): void
    {
        $this->actingAs($this->account())->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();
    }

    public static function mutationRoutes(): array
    {
        return [['/login'], ['/register'], ['/logout']];
    }

    #[DataProvider('mutationRoutes')]
    public function test_auth_mutations_reject_missing_or_invalid_csrf(string $path): void
    {
        // Exercise actual middleware, which otherwise skips CSRF in PHPUnit.
        $this->app->instance('env', 'local');
        if ($path === '/logout') {
            $this->actingAs($this->account());
        }
        $this->withSession(['_token' => 'valid-session-token']);
        $this->post($path, [])->assertStatus(419);
        $this->post($path, ['_token' => 'wrong-token'])->assertStatus(419);
    }

    public function test_login_and_logout_accept_valid_csrf_tokens(): void
    {
        $user = $this->account();
        $this->app->instance('env', 'local');
        $this->withSession(['_token' => 'valid-session-token']);
        $this->post('/login', ['_token' => 'valid-session-token', 'email' => $user->email, 'password' => self::PASSWORD])->assertRedirect('/user/dashboard');
        $this->post('/logout', ['_token' => session()->token()])->assertRedirect('/login');
        $this->assertGuest();
    }
}
