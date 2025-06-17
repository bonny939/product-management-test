<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * Get all active products
     */
    public function getAllActive(): Collection;

    /**
     * Get all trashed products
     */
    public function getAllTrashed(): Collection;

    /**
     * Create a new product
     */
    public function create(array $data): Product;

    /**
     * Update a product
     */
    public function update(Product $product, array $data): Product;

    /**
     * Delete a product (soft delete)
     */
    public function delete(Product $product): bool;

    /**
     * Permanently delete a product
     */
    public function forceDelete(Product $product): bool;

    /**
     * Restore a soft deleted product
     */
    public function restore(Product $product): bool;

    /**
     * Bulk delete products
     */
    public function bulkDelete(array $ids): int;

    /**
     * Bulk restore products
     */
    public function bulkRestore(array $ids): int;

    /**
     * Get total value sum for active products
     */
    public function getTotalValueSum(): float;

    /**
     * Export products to JSON
     */
    public function exportToJson(): string;

    /**
     * Export products to XML
     */
    public function exportToXml(): string;
}