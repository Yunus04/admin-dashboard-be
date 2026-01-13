<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_is_super_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'super_admin',
        ]);

        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isMerchant());
    }

    public function test_user_is_admin(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isMerchant());
    }

    public function test_user_is_merchant(): void
    {
        $user = User::factory()->create([
            'role' => 'merchant',
        ]);

        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isMerchant());
    }

    public function test_user_can_create_api_tokens(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token');

        $this->assertNotEmpty($token->plainTextToken);
        $this->assertCount(1, $user->tokens);
    }

    public function test_password_is_hashed(): void
    {
        $plainPassword = 'test password123';
        $user = User::factory()->create([
            'password' => $plainPassword,
        ]);

        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(password_verify($plainPassword, $user->password));
    }
}

