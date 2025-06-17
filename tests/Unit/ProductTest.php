<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product total value calculation
     */
    public function test_calculates_total_value_automatically(): void
    {
        $product = new Product([
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 25.99,
        ]);

        $product->calculateTotalValue();

        $this->assertEquals(259.90, $product->total_value);
    }

    /**
     * Test product total value calculation on save
     */
    public function test_calculates_total_value_on_save(): void
    {
        $product = Product::create([
            'name' => 'Test Product',
            'quantity' => 5,
            'price' => 12.50,
        ]);

        $this->assertEquals(62.50, $product->total_value);
    }

    /**
     * Test product scopes
     */
    public function test_active_scope(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->count(2)->deleted()->create();

        $activeProducts = Product::active()->get();
        $this->assertCount(3, $activeProducts);
    }

    /**
     * Test ordered by date scope
     */
    public function test_ordered_by_date_scope(): void
    {
        $product1 = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $product2 = Product::factory()->create(['created_at' => now()->subDay()]);
        $product3 = Product::factory()->create(['created_at' => now()]);

        $products = Product::orderedByDate()->get();

        $this->assertEquals($product3->id, $products->first()->id);
        $this->assertEquals($product1->id, $products->last()->id);
    }

    /**
     * Test product attributes casting
     */
    public function test_attribute_casting(): void
    {
        $product = Product::factory()->create([
            'quantity' => '10',
            'price' => '25.99',
        ]);

        $this->assertIsInt($product->quantity);
        $this->assertIsString($product->price); // Decimal cast returns string
        $this->assertIsString($product->total_value);
    }

    /**
     * Test soft deletes
     */
    public function test_soft_deletes(): void
    {
        $product = Product::factory()->create();
        $productId = $product->id;

        $product->delete();

        $this->assertSoftDeleted('products', ['id' => $productId]);
        $this->assertNotNull($product->fresh()->deleted_at);
    }
}