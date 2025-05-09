<?php

namespace Tests\Feature\Buyer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $seller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()
            ->state(['role' => 'buyer'])
            ->create();

        $this->seller = User::factory()
            ->state(['role' => 'seller'])
            ->create();

        $this->actingAs($this->buyer);
    }

    public function test_buyer_can_list_their_orders(): void
    {
        // Create some products
        $products = Product::factory()
            ->count(3)
            ->for($this->seller)
            ->create();

        // Create orders for the buyer
        foreach ($products as $product) {
            Order::factory()
                ->for($this->buyer, 'buyer')
                ->create([
                    'product_id' => $product->id,
                    'status' => 'pending'
                ]);
        }

        $response = $this->getJson('/api/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'user_id',
                        'product_id',
                        'status',
                        'payment_status'
                    ]
                ],
                'links' => [
                    'self'
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_buyer_can_create_new_order(): void
    {
        $product = Product::factory()
            ->for($this->seller)
            ->create();

        $response = $this->postJson('/api/orders', [
            'product_id' => $product->id
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Order created with id - 1'
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'product_id' => $product->id,
            'status' => 'new'
        ]);
    }

    public function test_buyer_cannot_create_order_with_invalid_product(): void
    {
        $response = $this->postJson('/api/orders', [
            'product_id' => 999 // Non-existent product ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    public function test_buyer_cannot_create_order_without_product_id(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }
}
