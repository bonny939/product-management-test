<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    /**
     * Show the main products page
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Get all active products via API
     */
    public function apiIndex(): JsonResponse
    {
        try {
            $data = $this->productService->getActiveProductsWithTotal();

            return response()->json([
                'success' => true,
                'data' => new ProductCollection($data['products']),
                'total_sum' => number_format($data['total_sum'], 2),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get trashed products
     */
    public function trash(): JsonResponse
    {
        try {
            $products = $this->productService->getTrashedProducts();

            return response()->json([
                'success' => true,
                'data' => new ProductCollection($products),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch trashed products: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trashed products',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a new product
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => new ProductResource($product),
            ], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Failed to create product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->productService->updateProduct($product, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($updatedProduct),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a product (soft delete)
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            $this->productService->deleteProduct($product);

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft deleted product
     */
    public function restore(Request $request): JsonResponse
    {
        try {
            $product = Product::onlyTrashed()->findOrFail($request->id);
            $this->productService->restoreProduct($product);

            return response()->json([
                'success' => true,
                'message' => 'Product restored successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to restore product: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore product',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:products,id',
            ]);

            $deletedCount = $this->productService->bulkDeleteProducts($request->ids);

            return response()->json([
                'success' => true,
                'message' => "$deletedCount products deleted successfully",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Failed to bulk delete products: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete products',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk restore products
     */
    public function bulkRestore(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer',
            ]);

            $restoredCount = $this->productService->bulkRestoreProducts($request->ids);

            return response()->json([
                'success' => true,
                'message' => "$restoredCount products restored successfully",
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Failed to bulk restore products: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore products',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Export products
     */
    public function export(Request $request): Response
    {
        try {
            $request->validate([
                'format' => 'required|in:json,xml',
            ]);

            $data = $this->productService->exportProducts($request->format);
            $filename = 'products_' . date('Y-m-d_H-i-s') . '.' . $request->format;

            $contentType = $request->format === 'json'
                ? 'application/json'
                : 'application/xml';

            return response($data)
                ->header('Content-Type', $contentType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            Log::error('Failed to export products: ' . $e->getMessage());

            return response('Failed to export products', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}