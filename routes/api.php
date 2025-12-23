<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{identifier}', [StoreController::class, 'show']);
    Route::get('/stores/{store}/products', [ProductController::class, 'index']);
    Route::get('/products/latest', [ProductController::class, 'latest']);
    Route::get('/products/{identifier}', [ProductController::class, 'show']);
});
