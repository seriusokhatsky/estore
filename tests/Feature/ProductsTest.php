<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Product;

class ProductsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_show_products(): void
    {

        Product::factory()->count(10)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');

    }
}
