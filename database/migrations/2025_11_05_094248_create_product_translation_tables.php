<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create product_translations table
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('lang', 2); // 'en', 'fa', etc.
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('company_name')->nullable();
            $table->string('material')->nullable();
            $table->string('chrome_plating')->nullable();

            // Add unique constraint for lang and slug
            $table->unique(['lang', 'slug']);
            // Add unique constraint for product and lang
            $table->unique(['product_id', 'lang']);
        });

        // 2. Create product_category_translations table
        Schema::create('product_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->constrained()->onDelete('cascade');
            $table->string('lang', 2);
            $table->string('name');
            $table->string('slug');

            $table->unique(['lang', 'slug']);
            $table->unique(['product_category_id', 'lang']);
        });

        // 3. Move existing data (Assuming 'fa' as default for old data)
        // Adjust 'fa' if your default language is different
        $defaultLang = 'fa';

        // Move Product data
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            DB::table('product_translations')->insert([
                'product_id'     => $product->id,
                'lang'           => $defaultLang,
                'title'          => $product->title,
                'slug'           => $product->slug,
                'description'    => $product->description,
                'company_name'   => $product->company_name,
                'material'       => $product->material,
                'chrome_plating' => $product->chrome_plating,
            ]);
        }

        // Move ProductCategory data
        $categories = DB::table('product_categories')->get();
        foreach ($categories as $category) {
            DB::table('product_category_translations')->insert([
                'product_category_id' => $category->id,
                'lang'                => $defaultLang,
                'name'                => $category->name,
                'slug'                => $category->slug,
            ]);
        }

        // 4. Drop old columns from main tables
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'slug',
                'description',
                'company_name',
                'material',
                'chrome_plating',
            ]);
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add columns (data will be lost, which is normal for a rollback)
        Schema::table('products', function (Blueprint $table) {
            $table->string('title')->after('author_id');
            $table->string('slug')->after('title')->unique();
            $table->text('description')->nullable()->after('slug');
            $table->string('company_name')->nullable()->after('description');
            $table->string('material')->nullable()->after('company_name');
            $table->string('chrome_plating')->nullable()->after('material');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('slug')->after('name')->unique();
        });

        // (You would need to write logic to move data back, but for a rollback,
        // it's often acceptable to just revert the schema)

        Schema::dropIfExists('product_translations');
        Schema::dropIfExists('product_category_translations');
    }
};
