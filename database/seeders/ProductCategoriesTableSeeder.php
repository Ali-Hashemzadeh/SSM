<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Media; // <-- Import Media
use Illuminate\Support\Facades\DB;

class ProductCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 2. Clear the tables first
        ProductCategory::truncate();
        DB::table('product_category_translations')->truncate();
        // Clear only media links for ProductCategories
        DB::table('mediables')->where('mediable_type', 'App\Models\ProductCategory')->delete();

        // 3. Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // 4. Check if media exists (based on your SQL dump, IDs 1, 2, 3 exist)
        $media1 = Media::find(5);

        if (!$media1) {
            $this->command->warn('Default media (IDs 1, 2, 3) not found. Categories will be created without images.');
        }

        $categories = [
            [
                'media_id' => $media1 ? $media1->id : null,
                'fa' => ['name' => 'پنل‌های ام‌دی‌اف', 'slug' => 'mdf-panels-fa'],
                'en' => ['name' => 'MDF Panels', 'slug' => 'mdf-panels-en'],
            ],
            [
                'media_id' => $media1 ? $media1->id : null,
                'fa' => ['name' => 'پنل‌های های‌گلاس', 'slug' => 'high-gloss-panels-fa'],
                'en' => ['name' => 'High-Gloss Panels', 'slug' => 'high-gloss-panels-en'],
            ],
            [
                'media_id' => $media1 ? $media1->id : null,
                'fa' => ['name' => 'روکش چوب', 'slug' => 'wood-veneer-fa'],
                'en' => ['name' => 'Wood Veneer', 'slug' => 'wood-veneer-en'],
            ],
        ];

        foreach ($categories as $categoryData) {
            // 1. Create the parent category
            $category = ProductCategory::create();

            // 2. Create the translations
            $category->translations()->create([
                'lang' => 'fa',
                'name' => $categoryData['fa']['name'],
                'slug' => $categoryData['fa']['slug'],
            ]);

            $category->translations()->create([
                'lang' => 'en',
                'name' => $categoryData['en']['name'],
                'slug' => $categoryData['en']['slug'],
            ]);

            // 3. Attach the single media item, if it exists
            if ($categoryData['media_id']) {
                $category->media()->sync([$categoryData['media_id']]);
            }
        }
    }
}
