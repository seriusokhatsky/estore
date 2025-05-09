<?php

namespace Tests\Feature\Buyer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Payment;

class PaymentsTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;
    protected $product;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $this->seller = User::factory()
            ->state(['role' => 'seller'])
            ->create();

        $this->product = Product::factory()
            ->for($this->seller)
            ->create(['price' => 99.99]);

        $this->order = Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $this->product->id,
                'status' => 'new'
            ]);

        $this->actingAs($this->buyer);
    }

    public function test_buyer_can_create_payment_for_their_order(): void
    {
        $response = $this->postJson('/api/payments', [
            'order_id' => $this->order->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment created with id - 1'
            ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $this->order->id,
            'status' => 'pending',
            'amount' => 99.99,
            'payment_method' => 'credit-card'
        ]);
    }

    public function test_buyer_cannot_create_payment_without_order_id(): void
    {
        $response = $this->postJson('/api/payments', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_buyer_cannot_create_payment_for_nonexistent_order(): void
    {
        $response = $this->postJson('/api/payments', [
            'order_id' => 999
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    public function test_buyer_cannot_create_payment_for_another_buyers_order(): void
    {
        $otherBuyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $otherOrder = Order::factory()
            ->for($otherBuyer, 'buyer')
            ->create([
                'product_id' => $this->product->id,
                'status' => 'new'
            ]);

        $response = $this->postJson('/api/payments', [
            'order_id' => $otherOrder->id
        ]);

        $response->assertStatus(404);
    }

    public function test_buyer_cannot_create_duplicate_payment_for_order(): void
    {
        // Create first payment
        Payment::create([
            'order_id' => $this->order->id,
            'status' => 'pending',
            'amount' => $this->product->price,
            'payment_method' => 'credit-card'
        ]);

        // Try to create second payment
        $response = $this->postJson('/api/payments', [
            'order_id' => $this->order->id
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }
}
