<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_register_and_receives_a_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Jane Author',
            'email' => 'jane@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['access_token', 'token_type', 'user' => ['id', 'email', 'role']]);

        $this->assertDatabaseHas('users', ['email' => 'jane@example.com', 'role' => 'author']);
    }

    public function test_a_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'john@example.com']);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure(['access_token']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrong-password',
        ])->assertUnauthorized();
    }

    public function test_profile_requires_authentication(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}
