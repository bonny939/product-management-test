<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Get all active products ordered by date
     */
    public function getAllActive(): Collection
    {
        return Product::active()->orderedByDate()->get();
    }

    /**
     * Get all trashed products
     */
    public function getAllTrashed(): Collection
    {
        return Product::onlyTrashed()->orderedByDate()->get();
    }

    /**
     * Create a new product
     */
    public function create(array $data): Product
    {
        return Product::create($data);
    }

    /**
     * Update a product
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    /**
     * Soft delete a product
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Permanently delete a product
     */
    public function forceDelete(Product $product): bool
    {
        return $product->forceDelete();
    }

    /**
     * Restore a soft deleted product
     */
    public function restore(Product $product): bool
    {
        return $product->restore();
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(array $ids): int
    {
        return Product::whereIn('id', $ids)->delete();
    }

    /**
     * Bulk restore products
     */
    public function bulkRestore(array $ids): int
    {
        return Product::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    /**
     * Get total value sum for active products
     */
    public function getTotalValueSum(): float
    {
        return (float) Product::active()->sum('total_value');
    }

    /**
     * Export products to JSON
     */
    public function exportToJson(): string
    {
        $products = $this->getAllActive();
        return json_encode($products->toArray(), JSON_PRETTY_PRINT);
    }

    /**
     * Export products to XML
     */
    public function exportToXml(): string
    {
        $products = $this->getAllActive();
        $xml = new \SimpleXMLElement('<products/>');

        foreach ($products as $product) {
            $productElement = $xml->addChild('product');
            $productElement->addChild('id', (string) $product->id);
            $productElement->addChild('name', htmlspecialchars($product->name));
            $productElement->addChild('quantity', (string) $product->quantity);
            $productElement->addChild('price', (string) $product->price);
            $productElement->addChild('total_value', (string) $product->total_value);
            $productElement->addChild('created_at', $product->created_at->toISOString());
            $productElement->addChild('updated_at', $product->updated_at->toISOString());
        }

        return $xml->asXML();
    }
}