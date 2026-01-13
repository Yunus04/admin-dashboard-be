<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    // Test constants to avoid duplication
    protected const TEST_EMAIL = 'test@example.com';
    protected const TEST_PASSWORD = 'password123';
    protected const WRONG_PASSWORD = 'wrong_password';
    protected const LOGIN_ENDPOINT = '/api/auth/login';
    protected const LOGOUT_ENDPOINT = '/api/auth/logout';
    protected const REGISTER_ENDPOINT = '/api/auth/register';
    protected const FORGOT_PASSWORD_ENDPOINT = '/api/auth/forgot-password';

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => self::TEST_EMAIL,
            'password' => bcrypt(self::TEST_PASSWORD),
        ]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::TEST_EMAIL,
            'password' => self::TEST_PASSWORD,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'token',
                ],
            ]);

        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['data']['token']);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => self::TEST_EMAIL,
            'password' => bcrypt(self::TEST_PASSWORD),
        ]);

        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::TEST_EMAIL,
            'password' => self::WRONG_PASSWORD,
        ]);

        $response->assertUnauthorized()
            ->assertJson([
                'success' => false,
            ]);

        $this->assertFalse($response['success']);
        $this->assertNotEquals('auth.invalid_credentials', $response['message']);
    }

    public function test_user_cannot_login_with_missing_email(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'password' => self::TEST_PASSWORD,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_user_cannot_login_with_missing_password(): void
    {
        $response = $this->postJson(self::LOGIN_ENDPOINT, [
            'email' => self::TEST_EMAIL,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson(self::REGISTER_ENDPOINT, [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => self::TEST_PASSWORD,
                'password_confirmation' => self::TEST_PASSWORD,
                'role' => 'merchant',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'token',
                ],
            ]);

        $this->assertTrue($response['success']);
    }

    public function test_unauthenticated_user_cannot_register(): void
    {
        $response = $this->postJson(self::REGISTER_ENDPOINT, [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => self::TEST_PASSWORD,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(self::LOGOUT_ENDPOINT);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertTrue($response['success']);
        $this->assertCount(0, $user->fresh()->tokens);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson(self::LOGOUT_ENDPOINT);

        $response->assertUnauthorized();
    }

    public function test_password_reset_request_with_valid_email(): void
    {
        User::factory()->create([
            'email' => self::TEST_EMAIL,
        ]);

        $response = $this->postJson(self::FORGOT_PASSWORD_ENDPOINT, [
            'email' => self::TEST_EMAIL,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'reset_token',
                    'note',
                ],
            ]);
    }
}

