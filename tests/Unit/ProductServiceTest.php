<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;
use Mockery;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;
    private $mockRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->productService = new ProductService($this->mockRepository);
    }

    /**
     * Test getting active products with total
     */
    public function test_get_active_products_with_total(): void
    {
        $products = collect([
            Product::factory()->make(['total_value' => 100.00]),
            Product::factory()->make(['total_value' => 200.00]),
        ]);

        $this->mockRepository
            ->shouldReceive('getAllActive')
            ->once()
            ->andReturn($products);

        $this->mockRepository
            ->shouldReceive('getTotalValueSum')
            ->once()
            ->andReturn(300.00);

        $result = $this->productService->getActiveProductsWithTotal();

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('total_sum', $result);
        $this->assertEquals(300.00, $result['total_sum']);
    }

    /**
     * Test product creation with validation
     */
    public function test_create_product_with_valid_data(): void
    {
        $data = [
            'name' => 'Test Product',
            'quantity' => 10,
            'price' => 25.99,
        ];

        $product = Product::factory()->make($data);

        $this->mockRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($product);

        $result = $this->productService->createProduct($data);

        $this->assertEquals('Test Product', $result->name);
    }

    /**
     * Test product creation with invalid data
     */
    public function test_create_product_with_invalid_data(): void
    {
        $this->expectException(ValidationException::class);

        $invalidData = [
            'name' => '', // required
            'quantity' => -1, // min:0
            'price' => -5.99, // min:0
        ];

        $this->productService->createProduct($invalidData);
    }

    /**
     * Test export functionality
     */
    public function test_export_products_json(): void
    {
        $jsonData = json_encode(['test' => 'data']);

        $this->mockRepository
            ->shouldReceive('exportToJson')
            ->once()
            ->andReturn($jsonData);

        $result = $this->productService->exportProducts('json');

        $this->assertEquals($jsonData, $result);
    }

    /**
     * Test export with invalid format
     */
    public function test_export_products_invalid_format(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported export format');

        $this->productService->exportProducts('invalid');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}