<?php

namespace Tests\Feature\Seller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()
                        ->state(['role' => 'seller'])
                        ->create();

        $this->actingAs($this->user);
    }

    /**
     * A basic feature test example.
     */
    public function test_can_view_its_products(): void
    {
        Product::factory()
            ->count(3)
            ->for($this->user)
            ->create();

        $response = $this->getJson('/api/seller.dashboard/products');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
    }

    public function test_can_create_product(): void
    {
        $response = $this->postJson('/api/seller.dashboard/products', [
            'name' => fake()->name(),
            'description' => fake()->text(300),
            'file' => fake()->text(10) . '.pdf',
            'price' => fake()->randomFloat(2, 10, 300)
        ]);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('message')
        );
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()
            ->for($this->user)
            ->create();

        $response = $this->deleteJson('/api/seller.dashboard/products/' . $product->id);

        $response->assertStatus(200);
        $response->assertSeeText('1');
    }

    public function test_can_get_single(): void
    {
        Product::factory()
            ->count(13)
            ->for($this->user)
            ->create();

        $response = $this->getJson('/api/seller.dashboard/products/1');

        $response->assertStatus(200);
        $response->assertJsonPath('id', 1);
        $response
        ->assertJson(fn (AssertableJson $json) =>
            $json->has('id')
                ->has('name')
                ->has('description')
                ->has('user_id')
                ->has('file')
                ->has('price')
                ->etc()
        );
    }
}
