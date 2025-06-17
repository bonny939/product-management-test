<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test the products index page loads successfully
     */
    public function test_products_index_page_loads(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
    }

    /**
     * Test fetching all products via API
     */
    public function test_can_fetch_all_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'quantity',
                        'price',
                        'total_value',
                        'created_at',
                        'updated_at',
                        'is_deleted'
                    ]
                ],
                'meta' => [
                    'total_count',
                    'total_sum'
                ]
            ],
            'total_sum'
        ]);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test creating a new product
     */
    public function test_can_create_product(): void
    {
        $productData = [
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 25.99,
        ];

        $response = $this->postJson('/api/products', $productData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $response->assertJsonFragment(['name' => 'Test Product']);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 25.99,
            'total_value' => 259.90,
        ]);
    }

    /**
     * Test product validation
     */
    public function test_product_validation(): void
    {
        $invalidData = [
            'name' => '', // required
            'quantity' => -1, // min:0
            'price' => -5.99, // min:0
        ];

        $response = $this->postJson('/api/products', $invalidData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'quantity', 'price']);
    }

    /**
     * Test updating a product
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Original Product',
            'quantity' => 5,
            'price' => 10.00,
        ]);

        $updateData = [
            'name' => 'Updated Product',
            'quantity' => 15,
            'price' => 20.00,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'quantity' => 15,
            'price' => 20.00,
            'total_value' => 300.00,
        ]);
    }

    /**
     * Test soft deleting a product
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    /**
     * Test fetching trashed products
     */
    public function test_can_fetch_trashed_products(): void
    {
        Product::factory()->count(3)->create();
        Product::factory()->count(2)->deleted()->create();

        $response = $this->getJson('/api/products/trash');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $data = $response->json('data.data');
        $this->assertCount(2, $data);
    }

    /**
     * Test restoring a product
     */
    public function test_can_restore_product(): void
    {
        $product = Product::factory()->deleted()->create();

        $response = $this->postJson('/api/products/restore', ['id' => $product->id]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'deleted_at' => null,
        ]);
    }

    /**
     * Test bulk deleting products
     */
    public function test_can_bulk_delete_products(): void
    {
        $products = Product::factory()->count(3)->create();
        $ids = $products->pluck('id')->toArray();

        $response = $this->deleteJson('/api/products/bulk-delete', ['ids' => $ids]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($ids as $id) {
            $this->assertSoftDeleted('products', ['id' => $id]);
        }
    }

    /**
     * Test bulk restoring products
     */
    public function test_can_bulk_restore_products(): void
    {
        $products = Product::factory()->count(3)->deleted()->create();
        $ids = $products->pluck('id')->toArray();

        $response = $this->postJson('/api/products/bulk-restore', ['ids' => $ids]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        foreach ($ids as $id) {
            $this->assertDatabaseHas('products', [
                'id' => $id,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * Test exporting products as JSON
     */
    public function test_can_export_products_as_json(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->get('/api/products/export?format=json');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
        $response->assertHeader('content-disposition');

        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertCount(3, $data);
    }

    /**
     * Test exporting products as XML
     */
    public function test_can_export_products_as_xml(): void
    {
        Product::factory()->count(2)->create();

        $response = $this->get('/api/products/export?format=xml');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/xml');

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertCount(2, $xml->product);
    }

    /**
     * Test total value calculation
     */
    public function test_total_value_calculation(): void
    {
        $productData = [
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 15.50,
        ];

        $this->postJson('/api/products', $productData);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'total_value' => 155.00,
        ]);
    }

    /**
     * Test unique product name validation
     */
    public function test_product_name_must_be_unique(): void
    {
        Product::factory()->create(['name' => 'Existing Product']);

        $duplicateData = [
            'name' => 'Existing Product',
            'quantity' => 5,
            'price' => 10.00,
        ];

        $response = $this->postJson('/api/products', $duplicateData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }
}