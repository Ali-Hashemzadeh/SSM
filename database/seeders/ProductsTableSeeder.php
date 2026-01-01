<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Media; // <-- Make sure this is imported
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 2. Clear product tables (and related cart/order tables)
        DB::table('cart_items')->truncate();
        DB::table('order_items')->truncate();
        Product::truncate();
        DB::table('product_translations')->truncate();
        DB::table('product_category_product')->truncate();
        // Clear only media links for Products
        DB::table('mediables')->where('mediable_type', 'App\Models\Product')->delete();

        // 3. Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $author = User::first();
        if (!$author) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $categoryIds = ProductCategory::pluck('id');
        if ($categoryIds->isEmpty()) {
            $this->command->error('No product categories found. Run ProductCategorySeeder first.');
            return;
        }

        // --- UPDATED: Get the specific Media ID to link ---
        $mediaIdToLink = 1;
        $mediaExists = Media::where('id', $mediaIdToLink)->exists();
        if (!$mediaExists) {
            $this->command->error("Media with ID {$mediaIdToLink} not found. Cannot link products.");
            // Set to null so the seeder can continue without linking media
            $mediaIdToLink = null;
        }
        // ---

        DB::transaction(function () use ($author, $categoryIds, $mediaIdToLink) {
            for ($i = 1; $i <= 10; $i++) {

                $product = Product::create([
                    'author_id'  => $author->id,
                    'dimensions' => '1220x2800x16mm',
                    'status'     => 'published',
                ]);

                // 2. Create Farsi (fa) Translation
                $faTitle = "محصول آزمایشی شماره {$i}";
                $product->translations()->create([
                    'lang'           => 'fa',
                    'title'          => $faTitle,
                    'slug'           => Str::slug($faTitle, '-', 'fa') . "-{$i}",
                    'description'    => "این توضیحات فارسی برای محصول آزمایشی {$i} است.",
                    'company_name'   => 'شرکت نمونه',
                    'material'       => 'ام‌دی‌اف',
                    'chrome_plating' => 'فوق مات',
                ]);

                // 3. Create English (en) Translation
                $enTitle = "Test Product {$i}";
                $product->translations()->create([
                    'lang'           => 'en',
                    'title'          => $enTitle,
                    'slug'           => Str::slug($enTitle) . "-{$i}",
                    'description'    => "This is the English description for test product {$i}.",
                    'company_name'   => 'Sample Co.',
                    'material'       => 'MDF',
                    'chrome_plating' => 'Super-Matte',
                ]);

                // 4. Attach categories
                $product->productCategories()->sync(
                    $categoryIds->random(rand(1, 2))->toArray()
                );

                // --- 5. Attach Media (UPDATED) ---
                if ($mediaIdToLink) {
                    // Sync this product with the single media ID 1
                    $product->media()->sync([$mediaIdToLink]);
                }
                // ---
            }
        });
    }
}
