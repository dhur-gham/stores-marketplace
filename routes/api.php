<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SavedAddressController;
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

    // Public payment callback (webhook from PayTabs)
    Route::post('/payment/callback', [PaymentController::class, 'callback']);

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

        // Order routes (rate limited to prevent spam)
        Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:10,1'); // 10 orders per minute
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);

        // Payment routes (rate limited for security)
        Route::get('/payment/client-key', [PaymentController::class, 'getClientKey'])->middleware('throttle:20,1'); // 20 requests per minute
        Route::post('/payment/process', [PaymentController::class, 'process'])->middleware('throttle:5,1'); // 5 payments per minute
        Route::post('/payment/refund', [PaymentController::class, 'refund'])->middleware('throttle:3,1'); // 3 refunds per minute
        Route::post('/payment/void', [PaymentController::class, 'void'])->middleware('throttle:3,1'); // 3 voids per minute

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

        // Saved Addresses routes
        Route::apiResource('saved-addresses', SavedAddressController::class);
    });
});
