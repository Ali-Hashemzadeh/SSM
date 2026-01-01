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
        // MVP SEO: Only the absolute essentials for Google to index correctly.
        $basicSeo = [
            'meta_description', // Crucial for CTR in search results
            'meta_keywords',    // Simple keyword tracking
        ];

        $postTypes = [
            // --- 1. SLIDER (Home Page Visuals) ---
            // Simple: Just an image (handled by media), a link, and a button label.
            [
                'name' => 'slider',
                'title' => 'اسلایدر',
                'fields' => [
                    'link_url',       // Where the slide goes when clicked
                    'button_text',    // e.g., "Read More"
                ],
            ],


            // --- 3. ARTICLES (Blog/News) ---
            // The core dynamic content.
            [
                'name' => 'article',
                'title' => 'مقالات',
                'fields' => array_merge([
                    'summary',        // Short text for cards/lists (Excerpt)
                ], $basicSeo),
            ],

            // --- 4. SERVICES / PRODUCTS (The Offer) ---
            // A generic type to list what the business does or sells.
            [
                'name' => 'service',
                'title' => 'خدمات',
                'fields' => array_merge([
                ], $basicSeo),
            ],
            [
                'name' => 'faq',
                'title' => 'پرسش و پاسخ',
                'fields' => array_merge([])
            ]

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
