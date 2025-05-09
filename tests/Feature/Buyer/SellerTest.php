<?php

namespace Tests\Feature\Buyer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class SellerTest extends TestCase
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
    }

    public function test_can_list_sellers(): void
    {
        // Create multiple sellers
        User::factory()
            ->state(['role' => 'seller'])
            ->count(3)
            ->create();

        $response = $this->getJson('/api/seller');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                    ]
                ]
            ])
            ->assertJsonCount(4, 'data'); // Including the one from setUp
    }

    public function test_can_view_single_seller(): void
    {
        $response = $this->getJson('/api/seller/' . $this->seller->id);

        $response->assertStatus(200)
            ->assertJson([
                'id' => $this->seller->id,
                'name' => $this->seller->name,
                'email' => $this->seller->email,
                'role' => 'seller'
            ]);
    }

    public function test_returns_404_for_nonexistent_seller(): void
    {
        $response = $this->getJson('/api/seller/999');

        $response->assertStatus(404);
    }

    public function test_can_list_seller_products(): void
    {
        // Create products for the seller
        $products = Product::factory()
            ->count(3)
            ->for($this->seller)
            ->create();

        $response = $this->getJson('/api/seller/' . $this->seller->id . '/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'price',
                        'description'
                    ]
                ],
                'links' => [
                    'self'
                ]
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_returns_404_when_listing_products_for_nonexistent_seller(): void
    {
        $response = $this->getJson('/api/seller/999/products');

        $response->assertStatus(404);
    }

    public function test_seller_products_are_ordered_by_created_at_desc(): void
    {
        // Create products with different creation times
        $oldProduct = Product::factory()
            ->for($this->seller)
            ->create(['created_at' => now()->subDay()]);

        $newProduct = Product::factory()
            ->for($this->seller)
            ->create(['created_at' => now()]);

        $response = $this->getJson('/api/seller/' . $this->seller->id . '/products');

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $newProduct->id)
            ->assertJsonPath('data.1.id', $oldProduct->id);
    }

    public function test_can_view_single_product(): void
    {
        $product = Product::factory()
            ->for($this->seller)
            ->create([
                'name' => 'Test Product',
                'description' => 'Test Description',
                'price' => 99.99
            ]);

        $response = $this->getJson('/api/seller/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'name' => 'Test Product',
                    'description' => 'Test Description',
                    'price' => 99.99
                ]
            ]);
    }

    public function test_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/products/9911239');

        $response->assertStatus(404);
    }
}
