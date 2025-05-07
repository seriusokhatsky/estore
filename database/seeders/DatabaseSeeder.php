<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@estore.com',
            'role' => 'admin',
            'password' => Hash::make('Admin123123))')
        ]);

        User::factory()->count(200)->create();

        // Create sellers and their products
        User::factory()
            ->count(20)
            ->state(['role' => 'seller'])
            ->create()
            ->each(function ($seller) {
                Product::factory()
                    ->count(rand(0, 100))
                    ->state(['user_id' => $seller->id])
                    ->create();
            });

        // Create buyers with random orders
        User::factory()
            ->count(20)
            ->state(['role' => 'buyer'])
            ->create()
            ->each(function ($buyer) {
                // Get random products for orders
                $products = Product::inRandomOrder()->take(rand(1, 10))->get();

                // Create orders for each product
                $products->each(function ($product) use ($buyer) {
                    Order::factory()->create([
                        'user_id' => $buyer->id,
                        'product_id' => $product->id
                    ]);
                });
            });
    }
}
