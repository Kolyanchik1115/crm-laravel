<?php

declare(strict_types=1);

namespace Modules\Auth\tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Modules\Auth\src\Domain\Entities\User;
use Modules\Auth\src\Domain\Entities\Role;
use Modules\Auth\src\Domain\Enums\RoleName;
use Modules\Auth\src\Interfaces\Http\Api\V1\AuthController;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerAuthRoutes();

        // Create roles
        $userRole = Role::create(['name' => RoleName::USER->value]);
        $adminRole = Role::create(['name' => RoleName::ADMIN->value]);

        // User
        $this->user = User::create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->user->roles()->attach($userRole);

        // Admin
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $this->admin->roles()->attach($adminRole);
    }

    private function registerAuthRoutes(): void
    {
        if (!$this->app->routesAreCached()) {
            Route::post('/api/v1/auth/login', [AuthController::class, 'login']);
            Route::post('/api/v1/auth/register', [AuthController::class, 'register']);
            Route::get('/api/v1/auth/me', [AuthController::class, 'me']);
            Route::post('/api/v1/auth/logout', [AuthController::class, 'logout']);
            Route::post('/api/v1/auth/refresh', [AuthController::class, 'refresh']);
        }
    }

   #[Test]
    public function user_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'email',
                        'first_name',
                        'last_name',
                        'roles',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
            ]);
    }

    #[Test]
    public function user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    #[Test]
    public function user_can_get_own_profile_with_token(): void
    {
        // Сначала логинимся
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Запрашиваем профиль
        $response = $this->getJson('/api/v1/auth/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'user@example.com',
                    'first_name' => 'Regular',
                    'last_name' => 'User',
                ],
            ]);
    }

    #[Test]
    public function user_can_logout(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Logout
        $response = $this->postJson('/api/v1/auth/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
    }

    #[Test]
    public function user_can_refresh_token(): void
    {
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.access_token');

        // Refresh token
        $response = $this->postJson('/api/v1/auth/refresh', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);
    }

    #[Test]
    public function user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'first_name' => 'New',
            'last_name' => 'User',
        ]);
    }

    #[Test]
    public function user_cannot_register_with_existing_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
