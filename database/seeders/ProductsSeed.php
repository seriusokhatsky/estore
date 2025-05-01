<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductsSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 30; $i++) {
            DB::table('products')->insert([
                'name' => $faker->unique()->word(2, true),
                'description' => $faker->paragraph(3),
                'price' => $faker->randomFloat(2, 10, 500),
                'file' => 'product_' . $i . '.' . $faker->randomElement(['jpg', 'png', 'gif']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
