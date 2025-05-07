<?php

namespace Tests\Feature\Seller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()
                        ->state(['role' => 'seller'])
                        ->create();

        $this->actingAs($this->seller);
    }

    public function test_seller_can_logout(): void
    {
        $response = $this->getJson('/api/seller.dashboard/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Logged out successfully'
        ]);
    }

    public function test_unauthorized_user_cannot_access_seller_dashboard(): void
    {
        $buyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $this->actingAs($buyer);

        $response = $this->getJson('/api/seller.dashboard/products');

        $response->assertStatus(403);
    }
} 