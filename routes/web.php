<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('products.index');

Route::prefix('api')->group(function () {
    // Bulk operations routes
    Route::delete('/products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('api.products.bulk-delete');
    Route::post('/products/bulk-restore', [ProductController::class, 'bulkRestore'])->name('api.products.bulk-restore');
    Route::post('/products/restore', [ProductController::class, 'restore'])->name('api.products.restore');
    Route::get('/products/trash', [ProductController::class, 'trash'])->name('api.products.trash');
    Route::get('/products/export', [ProductController::class, 'export'])->name('api.products.export');

    // Single resource routes
    Route::get('/products', [ProductController::class, 'apiIndex'])->name('api.products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('api.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('api.products.destroy');
});