<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User permissions
            ['name' => 'users.view', 'description' => 'مشاهده کاربران'],
            ['name' => 'users.create', 'description' => 'ایجاد کاربر جدید'],
            ['name' => 'users.edit', 'description' => 'ویرایش کاربران موجود'],
            ['name' => 'users.delete', 'description' => 'حذف کاربران'],
            ['name' => 'users.delete.self', 'description' => 'حذف کاربران خود'],
            ['name' => 'users.edit.self', 'description' => 'ویرایش کاربران خود'],

            // Role permissions
            ['name' => 'roles.view', 'description' => 'مشاهده نقش‌ها'],
            ['name' => 'roles.create', 'description' => 'ایجاد نقش جدید'],
            ['name' => 'roles.edit', 'description' => 'ویرایش نقش‌های موجود'],
            ['name' => 'roles.delete', 'description' => 'حذف نقش‌ها'],

            // Category permissions
            ['name' => 'categories.view', 'description' => 'مشاهده دسته‌بندی‌ها'],
            ['name' => 'categories.create', 'description' => 'ایجاد دسته‌بندی جدید'],
            ['name' => 'categories.delete', 'description' => 'حذف دسته‌بندی‌ها'],
            ['name' => 'categories.delete.self', 'description' => 'حذف دسته‌بندی‌های خود'],

            // Tag permissions
            ['name' => 'tags.view', 'description' => 'مشاهده برچسب‌ها'],
            ['name' => 'tags.create', 'description' => 'ایجاد برچسب جدید'],
            ['name' => 'tags.delete', 'description' => 'حذف برچسب‌ها'],
            ['name' => 'tags.delete.self', 'description' => 'حذف برچسب‌های خود'],

            // Media permissions
            ['name' => 'media.view', 'description' => 'مشاهده فایل‌های رسانه‌ای'],
            ['name' => 'media.upload', 'description' => 'آپلود فایل‌های رسانه‌ای'],
            ['name' => 'media.delete', 'description' => 'حذف فایل‌های رسانه‌ای'],
            ['name' => 'media.delete.self', 'description' => 'حذف فایل‌های رسانه‌ای خود'],

            // Post permissions
            ['name' => 'posts.view', 'description' => 'مشاهده مطالب'],
            ['name' => 'posts.create', 'description' => 'ایجاد مطلب جدید'],
            ['name' => 'posts.edit', 'description' => 'ویرایش مطالب موجود'],
            ['name' => 'posts.edit.self', 'description' => 'ویرایش مطالب خود'],
            ['name' => 'posts.delete', 'description' => 'حذف مطالب'],
            ['name' => 'posts.delete.self', 'description' => 'حذف مطالب خود'],
            ['name' => 'posts.publish', 'description' => 'انتشار مطالب'],
            ['name' => 'posts.approve', 'description' => 'تایید مطالب برای انتشار'],

            // Comment permissions
            ['name' => 'comments.view', 'description' => 'مشاهده نظرات'],
            ['name' => 'comments.create', 'description' => 'ایجاد نظر جدید'],
            ['name' => 'comments.edit', 'description' => 'ویرایش نظرات موجود'],
            ['name' => 'comments.edit.self', 'description' => 'ویرایش نظرات خود'],
            ['name' => 'comments.delete', 'description' => 'حذف نظرات'],
            ['name' => 'comments.delete.self', 'description' => 'حذف نظرات خود'],
            ['name' => 'comments.approve', 'description' => 'تایید نظرات'],
            ['name' => 'comments.moderate', 'description' => 'نظارت بر نظرات (علامت‌گذاری به عنوان اسپم)'],

            // Dashboard permissions
            ['name' => 'dashboard.view', 'description' => 'مشاهده داشبورد مدیریتی'],
            ['name' => 'dashboard.analytics', 'description' => 'مشاهده آمار و تحلیل‌ها'],

            // Settings permissions
            ['name' => 'settings.view', 'description' => 'مشاهده تنظیمات سیستم'],
            ['name' => 'settings.edit', 'description' => 'ویرایش تنظیمات سیستم'],

            // Pages permissions
            ['name' => 'pages.view', 'description' => 'مشاهده صفحات'],
            ['name' => 'pages.create', 'description' => 'ایجاد صفحه جدید'],
            ['name' => 'pages.edit', 'description' => 'ویرایش صفحات موجود'],
            ['name' => 'pages.edit.self', 'description' => 'ویرایش صفحات خود'],
            ['name' => 'pages.delete', 'description' => 'حذف صفحات'],
            ['name' => 'pages.delete.self', 'description' => 'حذف صفحات خود'],
            ['name' => 'pages.publish', 'description' => 'انتشار صفحات'],

            // Menu permissions
            ['name' => 'menus.view', 'description' => 'مشاهده منوها'],
            ['name' => 'menus.create', 'description' => 'ایجاد منوی جدید'],
            ['name' => 'menus.edit', 'description' => 'ویرایش منوها'],
            ['name' => 'menus.delete', 'description' => 'حذف منوها'],

            // Translation permissions
            ['name' => 'translations.view', 'description' => 'مشاهده ترجمه‌ها'],
            ['name' => 'translations.edit', 'description' => 'ویرایش ترجمه‌ها'],

            // Product permissions
            ['name' => 'products.view', 'description' => 'مشاهده محصولات'],
            ['name' => 'products.create', 'description' => 'ایجاد محصول جدید'],
            ['name' => 'products.edit', 'description' => 'ویرایش محصولات'],
            ['name' => 'products.delete', 'description' => 'حذف محصولات'],

            // Product Category permissions
            ['name' => 'product_categories.view', 'description' => 'مشاهده دسته‌بندی‌های محصولات'],
            ['name' => 'product_categories.create', 'description' => 'ایجاد دسته‌بندی محصول'],
            ['name' => 'product_categories.edit', 'description' => 'ویرایش دسته‌بندی محصول'],
            ['name' => 'product_categories.delete', 'description' => 'حذف دسته‌بندی محصول'],

            // --- ADD THIS BLOCK ---
            // Order (Quote Request) permissions
            ['name' => 'orders.view', 'description' => 'مشاهده سفارشات (درخواست‌ها)'],
            ['name' => 'orders.edit', 'description' => 'ویرایش سفارشات (تغییر وضعیت)'],
            ['name' => 'orders.delete', 'description' => 'حذف سفارشات'],
            ['name' => 'system.logs', 'description' => 'مشاهده لاگ ها']
            // --- END ADD ---
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }
    }
}

