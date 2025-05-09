<?php

namespace Tests\Feature\Seller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    protected $seller;
    protected $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()
                        ->state(['role' => 'seller'])
                        ->create();

        $this->buyer = User::factory()
                        ->state(['role' => 'buyer'])
                        ->create();

        $this->actingAs($this->seller);
    }

    public function test_seller_can_list_their_orders(): void
    {
        // Create products for the seller
        $products = Product::factory()
            ->count(3)
            ->for($this->seller)
            ->create();

        // Create orders for these products
        foreach ($products as $product) {
            Order::factory()
                ->for($this->buyer, 'buyer')
                ->create([
                    'product_id' => $product->id,
                    'status' => 'pending'
                ]);
        }

        $response = $this->getJson('/api/seller.dashboard/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->hasAll(['0.id', '0.status', '0.product_id'])
                ->etc()
        );
    }

    public function test_seller_can_view_single_order(): void
    {
        $product = Product::factory()
            ->for($this->seller)
            ->create();

        $order = Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $product->id,
                'status' => 'pending'
            ]);

        $response = $this->getJson('/api/seller.dashboard/orders/' . $order->id);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->where('id', $order->id)
                ->where('status', 'pending')
                ->where('product_id', $product->id)
                ->etc()
        );
    }

    public function test_seller_cannot_view_other_sellers_orders(): void
    {
        $otherSeller = User::factory()
            ->state(['role' => 'seller'])
            ->create();

        $product = Product::factory()
            ->for($otherSeller)
            ->create();

        $order = Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $product->id,
                'status' => 'pending'
            ]);

        $response = $this->getJson('/api/seller.dashboard/orders/' . $order->id);

        $response->assertStatus(404);
    }

    public function test_seller_can_filter_orders_by_buyer_id(): void
    {
        // Create products for the seller
        $products = Product::factory()
            ->count(2)
            ->for($this->seller)
            ->create();

        // Create two buyers
        $targetBuyer = User::factory()->state(['role' => 'buyer'])->create();
        $otherBuyer = User::factory()->state(['role' => 'buyer'])->create();

        // Create orders for target buyer
        foreach ($products as $product) {
            Order::factory()
                ->for($targetBuyer, 'buyer')
                ->create([
                    'product_id' => $product->id,
                    'status' => 'pending'
                ]);
        }

        // Create order for other buyer
        Order::factory()
            ->for($otherBuyer, 'buyer')
            ->create([
                'product_id' => $products[0]->id,
                'status' => 'pending'
            ]);

        $response = $this->getJson("/api/seller.dashboard/orders?buyer_id={$targetBuyer->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson(fn (AssertableJson $json) =>
                $json->each(fn (AssertableJson $json) =>
                    $json->where('user_id', $targetBuyer->id)
                        ->etc()
                )
            );
    }

    public function test_seller_can_filter_orders_by_product_id(): void
    {
        // Create two products for the seller
        $targetProduct = Product::factory()->for($this->seller)->create();
        $otherProduct = Product::factory()->for($this->seller)->create();

        // Create orders for target product
        Order::factory()
            ->count(2)
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $targetProduct->id,
                'status' => 'pending'
            ]);

        // Create order for other product
        Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $otherProduct->id,
                'status' => 'pending'
            ]);

        $response = $this->getJson("/api/seller.dashboard/orders?product_id={$targetProduct->id}");

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJson(fn (AssertableJson $json) =>
                $json->each(fn (AssertableJson $json) =>
                    $json->where('product_id', $targetProduct->id)
                        ->etc()
                )
            );
    }

    public function test_seller_cannot_filter_orders_by_other_sellers_product(): void
    {
        // Create another seller with a product
        $otherSeller = User::factory()->state(['role' => 'seller'])->create();
        $otherProduct = Product::factory()->for($otherSeller)->create();

        // Create an order for the other seller's product
        Order::factory()
            ->for($this->buyer, 'buyer')
            ->create([
                'product_id' => $otherProduct->id,
                'status' => 'pending'
            ]);

        $response = $this->getJson("/api/seller.dashboard/orders?product_id={$otherProduct->id}");

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
} 