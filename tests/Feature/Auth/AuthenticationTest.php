<?php

namespace Tests\Feature\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Lockout;
use RateLimiter;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('home', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_ensure_is_not_rate_limited()
    {
        RateLimiter::shouldReceive('tooManyAttempts')->with(\Mockery::any(), 5)->andReturn(true);
        RateLimiter::shouldReceive('availableIn')->with(\Mockery::any())->andReturn(120);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(trans('auth.throttle', ['seconds' => 120, 'minutes' => 2]));

        $request = new LoginRequest();

        event(new Lockout($request));

        $request->ensureIsNotRateLimited();
    }
}
