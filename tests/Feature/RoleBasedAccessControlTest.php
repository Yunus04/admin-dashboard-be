<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    // Test constants to avoid duplication
    protected const BEARER_PREFIX = 'Bearer ';
    protected const DASHBOARD_ENDPOINT = '/api/dashboard';
    protected const USERS_ENDPOINT = '/api/users';
    protected const MERCHANTS_ENDPOINT = '/api/merchants';
    protected const MERCHANTS_RESOURCE_ENDPOINT = '/api/merchants/';
    protected const INVALID_TOKEN = 'invalid-token';

    public function test_super_admin_can_access_protected_routes(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::DASHBOARD_ENDPOINT);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_admin_can_access_protected_routes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::DASHBOARD_ENDPOINT);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_merchant_can_access_protected_routes(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        $token = $merchant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::DASHBOARD_ENDPOINT);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson(self::DASHBOARD_ENDPOINT);

        $response->assertUnauthorized();
    }

    public function test_super_admin_can_access_user_management(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::USERS_ENDPOINT);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_admin_cannot_access_user_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::USERS_ENDPOINT);

        $response->assertForbidden();
    }

    public function test_merchant_cannot_access_user_management(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        $token = $merchant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::USERS_ENDPOINT);

        $response->assertForbidden();
    }

    public function test_super_admin_can_create_users(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->postJson(self::USERS_ENDPOINT, [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'merchant',
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true]);
    }

    public function test_admin_cannot_create_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->postJson(self::USERS_ENDPOINT, [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'role' => 'merchant',
        ]);

        $response->assertForbidden();
    }

    public function test_super_admin_and_admin_can_create_merchants(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $token = $superAdmin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->postJson(self::MERCHANTS_ENDPOINT, [
            'user_id' => 1,
            'business_name' => 'Test Store',
            'phone' => '+6281234567890',
            'address' => '123 Test Street',
        ]);

        $response->assertCreated()
            ->assertJson(['success' => true]);
    }

    public function test_merchant_cannot_create_merchants(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        $token = $merchant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->postJson(self::MERCHANTS_ENDPOINT, [
            'user_id' => 1,
            'business_name' => 'Test Store',
            'phone' => '+6281234567890',
            'address' => '123 Test Street',
        ]);

        $response->assertForbidden();
    }

    public function test_merchant_cannot_access_other_merchant_data_by_id(): void
    {
        // Create two different merchants
        $merchant1 = User::factory()->create(['role' => 'merchant']);
        $merchant2 = User::factory()->create(['role' => 'merchant']);

        // Create merchant profiles for both
        \App\Models\Merchant::factory()->create(['user_id' => $merchant1->id]);
        $profile2 = \App\Models\Merchant::factory()->create(['user_id' => $merchant2->id]);

        // Merchant1 tries to access Merchant2's data
        $token = $merchant1->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::MERCHANTS_RESOURCE_ENDPOINT . $profile2->id);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
            ]);

        $this->assertFalse($response['success']);
        $this->assertNotEquals('merchants.forbidden_view', $response['message']);
    }

    public function test_merchant_can_access_own_merchant_data(): void
    {
        $merchant = User::factory()->create(['role' => 'merchant']);
        $profile = \App\Models\Merchant::factory()->create(['user_id' => $merchant->id]);

        $token = $merchant->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::MERCHANTS_RESOURCE_ENDPOINT . $profile->id);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $profile->id);
    }

    public function test_merchant_cannot_update_other_merchant_data(): void
    {
        $merchant1 = User::factory()->create(['role' => 'merchant']);
        $merchant2 = User::factory()->create(['role' => 'merchant']);

        $profile2 = \App\Models\Merchant::factory()->create(['user_id' => $merchant2->id]);

        $token = $merchant1->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->putJson(self::MERCHANTS_RESOURCE_ENDPOINT . $profile2->id, [
            'business_name' => 'Hacked Store Name',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
            ]);

        $this->assertFalse($response['success']);
        $this->assertNotEquals('merchants.forbidden_update', $response['message']);
    }

    public function test_merchant_can_only_see_own_merchant_in_list(): void
    {
        $merchant1 = User::factory()->create(['role' => 'merchant']);
        $merchant2 = User::factory()->create(['role' => 'merchant']);

        $profile1 = \App\Models\Merchant::factory()->create(['user_id' => $merchant1->id]);
        \App\Models\Merchant::factory()->create(['user_id' => $merchant2->id]);
        \App\Models\Merchant::factory()->create(['user_id' => $merchant2->id]);

        $token = $merchant1->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . $token,
        ])->getJson(self::MERCHANTS_ENDPOINT);

        $response->assertOk()
            ->assertJson(['success' => true]);

        // Merchant1 should only see their own merchant profile
        $data = $response->json('data');

        // Assert only 1 result exists
        $this->assertIsArray($data);
        $this->assertCount(1, $data);

        // Assert that the returned profile belongs to merchant1
        $this->assertEquals($merchant1->id, $data[0]['user_id']);
        $this->assertEquals($profile1->id, $data[0]['id']);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $response = $this->withHeaders([
            'Authorization' => self::BEARER_PREFIX . self::INVALID_TOKEN,
        ])->getJson(self::DASHBOARD_ENDPOINT);

        $response->assertUnauthorized();
    }
}

