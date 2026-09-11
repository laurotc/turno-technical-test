<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.name', 'User')
            ->assertJsonPath('user.email', 'user@example.com')
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_register_requires_unique_email_and_confirmed_password(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->postJson('/api/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('user.email', 'user@example.com')
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $token = $user->createToken('api')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', 'user@example.com');
    }

    public function test_user_endpoint_requires_token(): void
    {
        $response = $this->getJson('/api/user');

        $response
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_user_can_logout_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/logout');

        $response->assertNoContent();
        $this->assertDatabaseEmpty('personal_access_tokens');
    }

    public function test_logout_only_deletes_current_token(): void
    {
        $user = User::factory()->create();
        $currentToken = $user->createToken('current')->plainTextToken;
        $user->createToken('other-device');

        $response = $this
            ->withToken($currentToken)
            ->postJson('/api/logout');

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 1);
        $this->assertSame('other-device', PersonalAccessToken::query()->sole()->name);
    }
}
