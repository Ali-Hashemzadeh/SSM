<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostType;
use App\Models\User;
use App\Models\Media;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class BlogsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. Identify/Create COMMON Dependencies ---

        // Get Author User with ID 1 (Do not create the user)
        $authorId = 1;
        $authorUser = User::find($authorId);

        if (!$authorUser) {
            $this->command->error("User with ID {$authorId} not found. Please ensure the main admin user exists.");
            return;
        }

        $this->command->info("Using existing author: {$authorUser->first_name} {$authorUser->last_name} (ID: {$authorId})");

        // Get Articles Post Type (Post Type ID 6)
        $articlesPostType = PostType::where('name', 'articles')->first();

        if (!$articlesPostType) {
            $this->command->error("Post type 'articles' not found. Ensure PostTypeSeeder has been run.");
            return;
        }

        // --- 2. Define Languages to Seed ---
        $languages = ['fa', 'en'];

        // --- 3. Loop Through Each Language and Create Content ---
        foreach ($languages as $lang) {

            // --- Setup Language-Specific Variables ---
            if ($lang === 'fa') {
                $faker = Faker::create('fa_IR');
                $categoryName = 'مقالات عمومی';
                $categorySlug = 'maghalat-omomi';
                $mediaPath = '/sample/sample-fa.jpg';
                $mediaAlt = 'تصویر نمونه مقاله';
                $mediaCaption = 'این یک تصویر نمونه برای مقالات آزمایشی است';
                $loremParagraph = 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد.';
                $postTitlePrefix = 'مقاله آزمایشی شماره ';
                $metaCaptionPrefix = 'خلاصه‌ای از مقاله: ';

            } else { // 'en'
                $faker = Faker::create('en_US');
                $categoryName = 'General Articles';
                $categorySlug = 'general-articles';
                $mediaPath = '/sample/sample-en.jpg';
                $mediaAlt = 'Sample article image';
                $mediaCaption = 'This is a sample image for test articles';
                $loremParagraph = $faker->paragraph(10); // Generate English lorem
                $postTitlePrefix = 'Sample Article No. ';
                $metaCaptionPrefix = 'Article summary: ';
            }

            $this->command->info("--- Setting up for language: [{$lang}] ---");

            // --- Get or Create Language-Specific Dependencies ---

            // Get or Create Sample Media
            $sampleMedia = Media::firstOrCreate(
                ['file_path' => $mediaPath, 'file_type' => 'image/jpeg'],
                [
                    'uploader_id' => $authorUser->id,
                    'alt_text' => $mediaAlt,
                    'caption' => $mediaCaption
                ]
            );

            // Get or Create Category
            $articleCategory = Category::firstOrCreate(
                ['slug' => $categorySlug, 'post_type_id' => $articlesPostType->id],
                [
                    'name' => $categoryName,
                    'creator_id' => $authorUser->id,
                    'lang' => $lang // Assign correct lang
                ]
            );

            // --- Create 20 Article Posts for this language ---

            $this->command->info("Creating 20 '{$lang}' article posts...");

            // 3 Paragraphs of Content
            $longContent = $loremParagraph . "\n\n" . $loremParagraph . "\n\n" . $loremParagraph;

            // Reset unique generator for this faker instance
            $faker->unique(true);

            for ($i = 1; $i <= 20; $i++) {
                $title = $postTitlePrefix . $i . ' - ' . $faker->sentence(rand(3, 7));
                $publishedDate = now()->subDays(rand(1, 100));

                $post = Post::create([
                    'title' => $title,
                    'slug' => Str::slug($title) . '-' . $faker->unique()->randomNumber(5),
                    'content' => $longContent,
                    'is_published' => true,
                    'status' => 'Approved',
                    'published_at' => $publishedDate,
                    'author_id' => $authorUser->id,
                    'post_type_id' => $articlesPostType->id,
                    'seen' => rand(100, 5000),
                    'meta' => [
                        'caption' => $metaCaptionPrefix . $faker->sentence(10),
                        // Format date based on language
                        'date' => $lang === 'fa'
                            ? verta($publishedDate)->format('Y/m/d')
                            : $publishedDate->format('Y/m/d'),
                    ],
                    'lang' => $lang, // Assign correct lang
                ]);

                // Link to Media
                $post->media()->attach($sampleMedia->id);

                // Link to Category
                $post->categories()->attach($articleCategory->id);

                if ($i % 5 == 0) { // Log progress every 5 posts
                    $this->command->info("Created {$lang} post {$i}/20: " . $title);
                }
            }
        }

        $this->command->info('---------------------------------');
        $this->command->info('Seeding of 40 articles (20 FA, 20 EN) was successful!');
    }
}
