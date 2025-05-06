<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;

class OrdersTest extends TestCase
{    
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()
                        ->state(['role' => 'admin'])
                        ->create();

        $this->actingAs($this->user);
    }
    /**
     * A basic feature test example.
     */
    public function test_admin_can_list_orders(): void
    {
        Order::factory()->count(10)->create();

        $response = $this->getJson('/api/admin/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(10);
        $response->assertJsonPath('0.status', 'pending');
        $response->assertJsonPath('2.id', 3);
    }

    public function test_admin_can_delete_order(): void
    {
        Order::factory()->count(10)->create();
        $response = $this->deleteJson('/api/admin/orders/1');
        $response->assertStatus(200);
        $response->assertSeeText('1');
        $this->assertDatabaseMissing('orders', ['id' => 1]);
    }
}
