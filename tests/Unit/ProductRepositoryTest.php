<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository();
    }

    /**
     * Test getting all active products
     */
    public function test_get_all_active(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->count(2)->deleted()->create();

        $products = $this->repository->getAllActive();

        $this->assertCount(3, $products);
        $products->each(function ($product) {
            $this->assertNull($product->deleted_at);
        });
    }

    /**
     * Test getting all trashed products
     */
    public function test_get_all_trashed(): void
    {
        Product::factory()->count(2)->create();
        Product::factory()->count(3)->deleted()->create();

        $products = $this->repository->getAllTrashed();

        $this->assertCount(3, $products);
        $products->each(function ($product) {
            $this->assertNotNull($product->deleted_at);
        });
    }

    /**
     * Test creating a product
     */
    public function test_create_product(): void
    {
        $data = [
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 25.99,
        ];

        $product = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(259.90, $product->total_value);
    }

    /**
     * Test updating a product
     */
    public function test_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original',
            'quantity' => 5,
            'price' => 10.00,
        ]);

        $updateData = [
            'name' => 'Updated',
            'quantity' => 15,
            'price' => 20.00,
        ];

        $updatedProduct = $this->repository->update($product, $updateData);

        $this->assertEquals('Updated', $updatedProduct->name);
        $this->assertEquals(300.00, $updatedProduct->total_value);
    }

    /**
     * Test bulk operations
     */
    public function test_bulk_delete(): void
    {
        $products = Product::factory()->count(3)->create();
        $ids = $products->pluck('id')->toArray();

        $deletedCount = $this->repository->bulkDelete($ids);

        $this->assertEquals(3, $deletedCount);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('products', ['id' => $id]);
        }
    }

    /**
     * Test bulk restore
     */
    public function test_bulk_restore(): void
    {
        $products = Product::factory()->count(3)->deleted()->create();
        $ids = $products->pluck('id')->toArray();

        $restoredCount = $this->repository->bulkRestore($ids);

        $this->assertEquals(3, $restoredCount);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('products', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Test total value sum calculation
     */
    public function test_get_total_value_sum(): void
    {
        Product::factory()->create(['total_value' => 100.00]);
        Product::factory()->create(['total_value' => 200.00]);
        Product::factory()->deleted()->create(['total_value' => 50.00]); // Deleted, shouldn't count

        $totalSum = $this->repository->getTotalValueSum();

        $this->assertEquals(300.00, $totalSum);
    }

    /**
     * Test JSON export
     */
    public function test_export_to_json(): void
    {
        Product::factory()->count(2)->create();

        $json = $this->repository->exportToJson();
        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertCount(2, $data);
        $this->assertArrayHasKey('name', $data[0]);
        $this->assertArrayHasKey('quantity', $data[0]);
        $this->assertArrayHasKey('price', $data[0]);
    }

    /**
     * Test XML export
     */
    public function test_export_to_xml(): void
    {
        Product::factory()->count(2)->create();

        $xml = $this->repository->exportToXml();
        $xmlObject = simplexml_load_string($xml);

        $this->assertNotFalse($xmlObject);
        $this->assertCount(2, $xmlObject->product);
        $this->assertObjectHasAttribute('name', $xmlObject->product[0]);
        $this->assertObjectHasAttribute('quantity', $xmlObject->product[0]);
        $this->assertObjectHasAttribute('price', $xmlObject->product[0]);
    }
}