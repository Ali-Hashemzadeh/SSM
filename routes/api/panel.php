<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Panel\UsersController;
use App\Http\Controllers\Api\Panel\RolesController;
use App\Http\Controllers\Api\Panel\PermissionsController;
use App\Http\Controllers\Api\Panel\MediasController;
use App\Http\Controllers\Api\Panel\PostsController;
use App\Http\Controllers\Api\Panel\CategoryController;
use App\Http\Controllers\Api\Panel\TagController;
use App\Http\Controllers\Api\Panel\CommentsController;
use App\Http\Controllers\Api\Panel\SettingsController;
use App\Http\Controllers\Api\Panel\SlidersController;
use App\Http\Controllers\Api\Panel\PagesController;
use App\Http\Controllers\Api\Panel\MenusController;
use App\Http\Controllers\Api\Panel\TranslationsController;
// --- ADD THIS LINE ---
use App\Http\Controllers\Api\Panel\ProductController;
use App\Http\Controllers\Api\Panel\ProductCategoryController;
// --- ADD THIS LINE FOR ORDERS ---
use App\Http\Controllers\Api\Panel\OrderController as PanelOrderController;
use App\Http\Controllers\Api\Panel\ActivityLogController;

Route::middleware('auth:sanctum')->prefix('activity-logs')->group(function () {
    // View all logs
    Route::get('/', [ActivityLogController::class, 'index'])
        ->middleware('permission:system.logs'); // or just remove middleware if not needed yet

    // View single log
    Route::get('/{id}', [ActivityLogController::class, 'show'])
        ->middleware('permission:system.logs');

    // Delete a log
    Route::delete('/{id}', [ActivityLogController::class, 'destroy'])
        ->middleware('permission:system.logs');
});


Route::prefix('panel')->group(function () {
    Route::middleware('auth:sanctum')->prefix('users')->group(function () {
        Route::get('/my', [UsersController::class, 'my']);
        Route::post('/update/{id}', [UsersController::class, 'update'])->middleware('permission:users.edit');
        Route::get('/', [UsersController::class, 'index'])->middleware('permission:users.view');
        Route::get('/{id}', [UsersController::class, 'show'])->middleware('permission:users.view');
        Route::post('/', [UsersController::class, 'store'])->middleware('permission:users.create');
        Route::delete('/{id}', [UsersController::class, 'destroy'])->middleware('permission:users.delete|users.delete.self');
    });

    Route::middleware('auth:sanctum')->prefix('roles')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->middleware('permission:roles.view');
        Route::get('/{id}', [RolesController::class, 'show'])->middleware('permission:roles.view');
        Route::post('/', [RolesController::class, 'store'])->middleware('permission:roles.create');
        Route::post('/update/{id}', [RolesController::class, 'update'])->middleware('permission:roles.edit');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->middleware('permission:roles.delete');
    });

    Route::middleware('auth:sanctum')->prefix('permissions')->group(function () {
        Route::get('/', [PermissionsController::class, 'index']);
    });

    Route::middleware('auth:sanctum')->prefix('media')->group(function () {
        // Admin routes for all media
        Route::get('/all', [MediasController::class, 'all'])->middleware('permission:media.view');
        Route::get('/all/{id}', [MediasController::class, 'showAny'])->middleware('permission:media.view');

        // User's own media routes (no permission needed)
        Route::get('/', [MediasController::class, 'index']);
        Route::get('/{id}', [MediasController::class, 'show']);
        Route::post('upload/', [MediasController::class, 'store'])->middleware('permission:media.upload');
        Route::delete('/{id}', [MediasController::class, 'destroy'])->middleware('permission:media.delete|media.delete.self');
    });

    Route::middleware('auth:sanctum')->prefix('posts')->group(function () {
        // Admin routes for all posts
        Route::get('/all', [PostsController::class, 'all'])->middleware('permission:posts.view');
        Route::get('/all/{id}', [PostsController::class, 'showAny'])->middleware('permission:posts.view');
        Route::get('/types', [PostsController::class, 'getPostTypes']);

        // User's own posts (no permission needed)
        Route::get('/', [PostsController::class, 'index']);
        Route::get('/{id}', [PostsController::class, 'show']);
        Route::post('/', [PostsController::class, 'store'])->middleware('permission:posts.create');
        Route::post('/update/{id}', [PostsController::class, 'update'])->middleware('permission:posts.edit|posts.edit.self');
        Route::delete('/{id}', [PostsController::class, 'destroy'])->middleware('permission:posts.delete|posts.delete.self');
        Route::post('/publish/{id}', [PostsController::class, 'publish'])->middleware('permission:posts.publish');
        Route::post('/update-status/{id}', [PostsController::class, 'updateStatus'])->middleware('permission:posts.approve');
        Route::post('/{id}/seen', [PostsController::class, 'markSeen']);
    });

    Route::middleware('auth:sanctum')->prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->middleware('permission:categories.view');
        Route::post('/', [CategoryController::class, 'store'])->middleware('permission:categories.create');
        Route::post('/update/{id}', [CategoryController::class, 'update'])->middleware('permission:categories.edit|categories.edit.self');
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->middleware('permission:categories.delete|categories.delete.self');
    });

    Route::middleware('auth:sanctum')->prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index'])->middleware('permission:tags.view');
        Route::post('/', [TagController::class, 'store'])->middleware('permission:tags.create');
        Route::post('/update/{id}', [TagController::class, 'update'])->middleware('permission:tags.edit|tags.edit.self');
        Route::delete('/{id}', [TagController::class, 'destroy'])->middleware('permission:tags.delete|tags.delete.self');
    });

    Route::middleware('auth:sanctum')->prefix('comments')->group(function () {
        Route::get('/', [CommentsController::class, 'index'])->middleware('permission:comments.view');
        Route::get('/my', [CommentsController::class, 'myComments']);
        Route::post('/update-status/{id}', [CommentsController::class, 'updateStatus'])->middleware('permission:comments.approve');
        Route::delete('/{id}', [CommentsController::class, 'destroy'])->middleware('permission:comments.delete|comments.delete.self');
    });

    Route::middleware('auth:sanctum')->prefix('settings')->group(function () {
        Route::get('/', [SettingsController::class, 'show'])->middleware('permission:settings.view');
        Route::get('/languages', [SettingsController::class, 'getLanguages'])->middleware('permission:settings.view');
        Route::post('/update', [SettingsController::class, 'update'])->middleware('permission:settings.edit');
    });

    Route::middleware('auth:sanctum')->prefix('sliders')->group(function () {
        Route::get('/', [SlidersController::class, 'index'])->middleware('permission:sliders.view');
        Route::get('/{id}', [SlidersController::class, 'show'])->middleware('permission:sliders.view');
        Route::post('/', [SlidersController::class, 'store'])->middleware('permission:sliders.create');
        Route::post('/update/{id}', [SlidersController::class, 'update'])->middleware('permission:sliders.edit');
        Route::delete('/{id}', [SlidersController::class, 'destroy'])->middleware('permission:sliders.delete');
    });

    Route::middleware('auth:sanctum')->prefix('pages')->group(function () {
        // Admin routes for all pages
        Route::get('/all', [PagesController::class, 'all'])->middleware('permission:pages.view');
        Route::get('/all/{id}', [PagesController::class, 'show'])->middleware('permission:pages.view');

        // User's own pages
        Route::get('/', [PagesController::class, 'index']);
        Route::get('/{slug}', [PagesController::class, 'show']);
        Route::post('/', [PagesController::class, 'store'])->middleware('permission:pages.create');
        Route::post('/update/{id}', [PagesController::class, 'update'])->middleware('permission:pages.edit|pages.edit.self');
        Route::delete('/{id}', [PagesController::class, 'destroy'])->middleware('permission:pages.delete|pages.delete.self');
        Route::post('/publish/{id}', [PagesController::class, 'publish'])->middleware('permission:pages.publish');
        Route::post('/update-status/{id}', [PagesController::class, 'updateStatus'])->middleware('permission:pages.approve');
    });

    Route::middleware('auth:sanctum')->prefix('menus')->group(function () {
        Route::post('/create', [MenusController::class, 'store'])->middleware('permission:menus.create');
        Route::get('/', [MenusController::class, 'index'])->middleware('permission:menus.view');
        Route::get('/{id}', [MenusController::class, 'show'])->middleware('permission:menus.view');
        Route::post('/update/{id}', [MenusController::class, 'update'])->middleware('permission:menus.edit');
        Route::delete('/{id}', [MenusController::class, 'destroy'])->middleware('permission:menus.delete');
    });

    Route::middleware('auth:sanctum')->prefix('translations')->group(function(){
        Route::get('/{locale}', [TranslationsController::class,'show'])->middleware('permission:translations.view');
        Route::post('/update/{locale}', [TranslationsController::class,'update'])->middleware('permission:translations.edit');
    });

    // --- [NEW] PRODUCTS MODULE (PANEL) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('products', ProductController::class)
            ->middleware([
                'permission:products.view|products.create|products.edit|products.delete'
            ]);

        Route::apiResource('product-categories', ProductCategoryController::class)
            ->middleware([
                'permission:product_categories.view|product_categories.create|product_categories.edit|product_categories.delete'
            ]);
    });
    // --- [NEW] ORDERS MODULE (PANEL) ---
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('orders', PanelOrderController::class)
            ->except(['store']) // Admins don't create orders, they only manage them
            ->middleware([
                'permission:orders.view|orders.edit|orders.delete'
            ]);
    });
});

