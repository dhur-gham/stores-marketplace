<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/stores', [StoreController::class, 'index']);
    Route::get('/stores/{identifier}/delivery-prices', [StoreController::class, 'deliveryPrices']);
    Route::get('/stores/{identifier}/products', [ProductController::class, 'index']);
    Route::get('/stores/{identifier}', [StoreController::class, 'show']);
    Route::get('/products', [ProductController::class, 'all']);
    Route::get('/products/latest', [ProductController::class, 'latest']);
    Route::get('/products/{identifier}', [ProductController::class, 'show']);
    Route::get('/cities', [CityController::class, 'index']);

    // Public wishlist share route
    Route::get('/wishlist/share/{token}', [WishlistController::class, 'shared']);

    // Auth routes (public)
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/user', [AuthController::class, 'user']);

        // Cart routes
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{cart_item}', [CartController::class, 'update']);
        Route::delete('/cart/{cart_item}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Order routes
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // Wishlist routes
        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{wishlist_item}', [WishlistController::class, 'destroy']);
        Route::get('/wishlist/check/{product_id}', [WishlistController::class, 'check']);

        // Wishlist share routes
        Route::get('/wishlist/share', [WishlistController::class, 'share']);
        Route::post('/wishlist/share', [WishlistController::class, 'share']);
        Route::put('/wishlist/share/message', [WishlistController::class, 'updateShareMessage']);
        Route::put('/wishlist/share/toggle', [WishlistController::class, 'toggleShare']);

        // Telegram routes
        Route::get('/telegram/activation-link', [TelegramController::class, 'getActivationLink']);
    });
});
