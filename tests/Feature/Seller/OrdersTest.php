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
} 