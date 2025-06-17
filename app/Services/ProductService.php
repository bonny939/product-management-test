<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get all active products with total sum
     */
    public function getActiveProductsWithTotal(): array
    {
        $products = $this->productRepository->getAllActive();
        $totalSum = $this->productRepository->getTotalValueSum();

        return [
            'products' => $products,
            'total_sum' => $totalSum,
        ];
    }

    /**
     * Get all trashed products
     */
    public function getTrashedProducts(): Collection
    {
        return $this->productRepository->getAllTrashed();
    }

    /**
     * Create a new product with validation
     */
    public function createProduct(array $data): Product
    {
        $this->validateProductData($data);
        return $this->productRepository->create($data);
    }

    /**
     * Update a product with validation
     */
    public function updateProduct(Product $product, array $data): Product
    {
        $this->validateProductData($data, $product->id);
        return $this->productRepository->update($product, $data);
    }

    /**
     * Delete a product
     */
    public function deleteProduct(Product $product): bool
    {
        return $this->productRepository->delete($product);
    }

    /**
     * Restore a product
     */
    public function restoreProduct(Product $product): bool
    {
        return $this->productRepository->restore($product);
    }

    /**
     * Bulk delete products
     */
    public function bulkDeleteProducts(array $ids): int
    {
        return $this->productRepository->bulkDelete($ids);
    }

    /**
     * Bulk restore products
     */
    public function bulkRestoreProducts(array $ids): int
    {
        return $this->productRepository->bulkRestore($ids);
    }

    /**
     * Export products to specified format
     */
    public function exportProducts(string $format): string
    {
        return match (strtolower($format)) {
            'json' => $this->productRepository->exportToJson(),
            'xml' => $this->productRepository->exportToXml(),
            default => throw new \InvalidArgumentException('Unsupported export format'),
        };
    }

    /**
     * Validate product data
     */
    private function validateProductData(array $data, ?int $excludeId = null): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:products,name' . ($excludeId ? ",$excludeId" : ''),
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}