<?php

use App\Http\Controllers\Api\Client\PostsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Client\HomeController;
use App\Http\Controllers\Api\Client\MenuController;
use App\Http\Controllers\Api\Client\SettingsController;
use App\Http\Controllers\Api\Client\TranslationsController;
use App\Http\Controllers\Api\Client\PagesController;
// --- ADD THESE LINES ---
use App\Http\Controllers\Api\Client\ProductController as ClientProductController;
use App\Http\Controllers\Api\Client\ProductCategoryController as ClientProductCategoryController;
use App\Http\Controllers\Api\Client\CartController;
use App\Http\Controllers\Api\Client\OrderController as ClientOrderController;


Route::prefix('client')->group(function () {
    Route::get('home', [HomeController::class, 'index']);
    Route::get('menu', [MenuController::class, 'index']);
    Route::get('posts', [PostsController::class, 'index']);
    Route::get('posts/{id}', [PostsController::class, 'show']);
    // Pages
    Route::get('pages', [PagesController::class, 'index']);
    Route::get('pages/{slug}', [PagesController::class, 'show']);
    Route::post('posts/{id}/comment', [PostsController::class, 'comment'])->middleware('auth:sanctum');
    Route::post('posts/{id}/seen', [PostsController::class, 'markSeen']);
    Route::get('settings', [SettingsController::class, 'show']);
    Route::get('post-types', [PostsController::class, 'getPostTypes']);
    // Translations
    Route::get('translations/{locale}', [TranslationsController::class, 'show']);
    Route::get('languages', [TranslationsController::class, 'languages']);

    // --- [NEW] PUBLIC PRODUCTS API (NO AUTH NEEDED) ---
    Route::get('products', [ClientProductController::class, 'index']);
    Route::get('products/{slug}', [ClientProductController::class, 'show']);
    Route::get('product-categories', [ClientProductCategoryController::class, 'index']);


    // --- [NEW] CART & ORDER API (AUTH REQUIRED) ---
    Route::middleware('auth:sanctum')->group(function () {
        // Cart
        // We use apiResource for index, store, update, destroy
        Route::apiResource('cart', CartController::class)
            ->except(['show']); // We only need index (GET /cart)

        // Order
        // GET /orders (history) and POST /orders (confirm cart)
        Route::apiResource('orders', ClientOrderController::class)
            ->only(['index', 'store']);
    });
    Route::middleware('auth:sanctum')->group(function () {
       Route::post('orderAndConfirm', [ClientOrderController::class, 'orderAndConfirm']);
    });
});

