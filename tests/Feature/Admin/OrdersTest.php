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
        $response->assertJsonPath('data.0.status', 'pending');
        $response->assertJsonPath('data.2.id', 3);
    }

    public function test_admin_can_delete_order(): void
    {
        Order::factory()->count(10)->create();
        $response = $this->deleteJson('/api/admin/orders/1');
        $response->assertStatus(200);
        $response->assertSeeText('1');
        $this->assertDatabaseMissing('orders', ['id' => 1]);
    }

    public function test_admin_can_update_order_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'processing'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order status updated successfully',
                'order' => [
                    'id' => $order->id,
                    'status' => 'processing'
                ]
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing'
        ]);
    }

    public function test_admin_cannot_update_order_with_invalid_status(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->patchJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'invalid_status'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending'
        ]);
    }
}
