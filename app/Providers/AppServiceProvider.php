<?php

namespace App\Providers;

use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\Cart\EloquentCartRepository;
use App\Repositories\Order\EloquentOrderRepository;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Product\EloquentProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductCategory\EloquentProductCategoryRepository;
use App\Repositories\ProductCategory\ProductCategoryRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Users\UserRepositoryInterface;
use App\Repositories\Users\UserRepository;
use App\Repositories\Posts\PostRepositoryInterface;
use App\Repositories\Posts\PostRepository;
use App\Repositories\Comments\CommentRepositoryInterface;
use App\Repositories\Comments\CommentRepository;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Repositories\Categories\CategoryRepository;
use App\Repositories\Tags\TagRepositoryInterface;
use App\Repositories\Tags\TagRepository;
use App\Repositories\Medias\MediaRepositoryInterface;
use App\Repositories\Medias\MediaRepository;
use App\Repositories\Roles\RoleRepositoryInterface;
use App\Repositories\Roles\RoleRepository;
use App\Repositories\Settings\SettingRepositoryInterface;
use App\Repositories\Settings\SettingRepository;
use App\Repositories\Pages\PageRepositoryInterface;
use App\Repositories\Pages\PageRepository;
use App\Repositories\Menus\MenuRepositoryInterface;
use App\Repositories\Menus\MenuRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(PageRepositoryInterface::class, PageRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, EloquentProductRepository::class);
        $this->app->bind(ProductCategoryRepositoryInterface::class, EloquentProductCategoryRepository::class);
        $this->app->bind(CartRepositoryInterface::class, EloquentCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
