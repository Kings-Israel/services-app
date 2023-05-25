<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
        $this->seed(CategorySeeder::class);
    }

    public function test_vendor_can_add_service(): void
    {
        $response = $this->actingAs($this->getUser('vendor'))->postJson('/api/services', [
            'title' => 'Test Service',
            'price_min' => '200',
            'price_max' => '500',
            'location_lat' => '-1.301253020',
            'location_long' => '36.797311306',
            'categories' => [1, 2]
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'title', 'price_min', 'price_max', 'location', 'location_lat', 'location_long', 'categories', 'images',
            ]);
    }

    public function test_user_can_view_services()
    {
        $this->actingAs($this->getUser('vendor'))->postJson('/api/services', [
            'title' => 'Test Service',
            'price_min' => '200',
            'price_max' => '500',
            'location_lat' => '-1.301253020',
            'location_long' => '36.797311306',
            'categories' => [1, 2]
        ]);

        $response = $this->getJson('/api/services');

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

    public function test_authenticated_user_can_bookmark_a_service()
    {
        $service = Service::factory()->create([
            'user_id' => $this->getUser('vendor')->id
        ]);

        $response = $this->actingAs($this->getUser('user'))->postJson('/api/bookmark-service', [
            'service_id' => $service->id
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'data' => [
                        'id', 'user_id', 'title', 'price_min', 'price_max', 'location', 'location_lat', 'location_long'
                    ]
                ]);
    }

    public function test_unauthenticated_user_cannot_bookmark_a_service()
    {
        $service = Service::factory()->create([
            'user_id' => $this->getUser('vendor')->id
        ]);

        $response = $this->postJson('/api/bookmark-service', [
            'service_id' => $service->id
        ]);

        $response->assertStatus(401)
                ->assertJsonStructure([
                    'message',
                ]);
    }

    // Get User depending on role
    private function getUser($role): User
    {
      $user = User::factory()->create();

      $user->assignRole($role);

      return $user;
    }
}
