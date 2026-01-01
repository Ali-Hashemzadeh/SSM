<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\PostType;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'education' => [
                ['name' => 'آموزش اسلامی', 'slug' => 'islamic-education'],
                ['name' => 'آموزش مدرن', 'slug' => 'modern-education'],
            ],
            'research' => [
                ['name' => 'مقالات', 'slug' => 'articles'],
                ['name' => 'پروژه‌ها', 'slug' => 'projects'],
            ],
            'news' => [
                ['name' => 'اطلاعیه‌ها', 'slug' => 'announcements'],
                ['name' => 'رویدادها', 'slug' => 'events'],
            ],
            'discourse' => [
                ['name' => 'مناظره‌ها', 'slug' => 'debates'],
                ['name' => 'انجمن‌ها', 'slug' => 'forums'],
            ],
            'gallery' => [
                ['name' => 'عکس', 'slug' => 'images'],
                ['name' => 'ویدیو', 'slug' => 'videos'],
                ['name' => 'صوت', 'slug' => 'sounds'],
            ],
        ];

        foreach ($categories as $postTypeSlug => $cats) {
            $postType = PostType::where('name', $postTypeSlug)->first();
            if ($postType) {
                foreach ($cats as $cat) {
                    Category::firstOrCreate([
                        'name' => $cat['name'],
                        'slug' => $cat['slug'],
                        'post_type_id' => $postType->id
                    ]);
                }
            }
        }
    }
} 