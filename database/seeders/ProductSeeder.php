<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 50 active products
        Product::factory()->count(50)->create();
        
        // Create 10 deleted products
        Product::factory()->count(10)->deleted()->create();
    }
}