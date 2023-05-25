<?php

namespace Tests\Feature\Auth;

use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_new_users_can_register_with_user_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@user.com',
            'phone_number' => '07071375647',
            'password' => 'password',
            'role' => 'user'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'first_name', 'last_name', 'email', 'phone_number'
                ],
                'token'
            ]);
    }

    public function test_new_users_can_register_with_vendor_role()
    {
        $response = $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'Vendor',
            'email' => 'test@vendor.com',
            'phone_number' => '07071375747',
            'password' => 'password',
            'role' => 'vendor'
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'first_name', 'last_name', 'email', 'phone_number'
                ],
                'token'
            ]);
    }
}
