<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PostType;

class PostTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $postTypes = [
            [
                'name' => 'slider',
                'title' => 'اسلایدر',
                'fields' => [
                    'title',
                    'co-title',
                    'caption'
                ],
            ],
            [
                'name' => 'services',
                'title' => 'خدمات',
                'fields' => [
                    'caption',
                ],
            ],
            [
                'name' => 'statistics',
                'title' => 'آمار',
                'fields' => [
                    'amount'
                ],
            ],
            [
                'name' => 'recent_products',
                'title' => 'نمونه کار های اخیر',
                'fields' => [
                    'tags'
                ],
            ],
            [
                'name' => 'customer_reviews',
                'title' => 'نظرات مشتریان',
                'fields' => [
                    'comment',
                    'customer_name',
                    'customer_name_caption'
                ],
            ],
            [
                'name' => 'articles',
                'title' => 'مقالات',
                'fields' => [
                    'caption',
                    'date',
                ],
            ],
        ];

        foreach ($postTypes as $type) {
            PostType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'title' => $type['title'],
                    'fields' => $type['fields'],
                ]
            );
        }
    }
}
