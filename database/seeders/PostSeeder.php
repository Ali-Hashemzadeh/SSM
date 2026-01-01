<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostType;
use App\Models\User;
use App\Models\Media;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- 1. Get Existing User ---
        $this->command->info("در حال یافتن کاربر نویسنده (موبایل: 09197238119)...");
        $authorUser = User::where('mobile', '09197238119')->first();

        if (!$authorUser) {
            $this->command->warn('کاربر 09197238119 یافت نشد! در حال ایجاد یک کاربر ادمین پیش‌فرض...');
            $authorUser = User::firstOrCreate(
                ['mobile' => '09197238119'],
                [
                    'first_name' => 'علی',
                    'last_name' => 'هاشم زاده',
                    'password' => bcrypt('password123'),
                    'role_id' => 1,
                    'creator_id' => 1,
                ]
            );
            $this->command->info("کاربر پیش‌فرض ایجاد شد.");
        } else {
            $this->command->info("استفاده از کاربر موجود: {$authorUser->first_name} {$authorUser->last_name}");
        }

        // --- 2. Create Single Reusable Media ---
        $sampleMedia = Media::firstOrCreate(
            ['file_path' => '/sample/sample.jpg', 'file_type' => 'image/jpeg'],
            [
                'uploader_id' => $authorUser->id,
                'alt_text' => 'تصویر نمونه',
                'caption' => 'این یک تصویر نمونه برای محتوای آزمایشی است'
            ]
        );

        // --- 3. Create Category for Articles ---
        $articleCategory = null;
        $articlesPostType = PostType::where('name', 'articles')->first();

        if ($articlesPostType) {
            $this->command->info('در حال ایجاد دسته‌بندی برای مقالات...');
            $articleCategory = Category::firstOrCreate(
                ['slug' => 'maghalat-omomi', 'post_type_id' => $articlesPostType->id],
                [
                    'name' => 'مقالات عمومی',
                    'creator_id' => $authorUser->id,
                    'lang' => 'fa'
                ]
            );
        }

        // --- 4. Define Persian Content ---
        $faker = \Faker\Factory::create('fa_IR'); // Keep for names and numbers

        $persianLoremIpsum = 'لورم ایپسوم متن ساختگی با تولید سادگی نامفهوم از صنعت چاپ، و با استفاده از طراحان گرافیک است، چاپگرها و متون بلکه روزنامه و مجله در ستون و سطرآنچنان که لازم است، و برای شرایط فعلی تکنولوژی مورد نیاز، و کاربردهای متنوع با هدف بهبود ابزارهای کاربردی می باشد، کتابهای زیادی در شصت و سه درصد گذشته حال و آینده، شناخت فراوان جامعه و متخصصان را می طلبد، تا با نرم افزارها شناخت بیشتری را برای طراحان رایانه ای علی الخصوص طراحان خلاقی، و فرهنگ پیشرو در زبان فارسی ایجاد کرد.';

        $longPersianLorem = $persianLoremIpsum . ' ' . $persianLoremIpsum . ' ' . $persianLoremIpsum . ' در این صورت می توان امید داشت که تمام و دشواری موجود در ارائه راهکارها، و شرایط سخت تایپ به پایان رسد و زمان مورد نیاز شامل حروفچینی دستاوردهای اصلی، و جوابگوی سوالات پیوسته اهل دنیای موجود طراحی اساسا مورد استفاده قرار گیرد.';

        $persianTitles = [
            'عنوان آزمایشی اول برای پست',
            'یک مطلب جدید درباره خدمات ما',
            'چرا باید ما را انتخاب کنید؟',
            'بررسی پروژه‌های اخیر شرکت',
            'راهنمای کامل خدمات طراحی',
            'مقاله ویژه درباره فناوری روز',
            'اسلایدر اصلی صفحه نخست',
            'معرفی خدمات جدید',
            'نظر مشتری برتر ماه',
        ];

        // --- 5. Get Post Types ---
        $postTypes = PostType::all();

        // --- 6. Loop Through Post Types and Create Posts ---
        foreach ($postTypes as $postType) {
            $this->command->info("در حال ایجاد پست برای نوع: {$postType->title}");
            $meta = [];
            $title = '';

            switch ($postType->name) {

                // --- Slider: Create 3 ---
                case 'slider':
                    $this->command->info('slider');
                    for ($i = 0; $i < 3; $i++) {
                        $title = $persianTitles[array_rand($persianTitles)];
                        $meta = [
                            'co-title' => 'یک عنوان فرعی جذاب برای اسلایدر',
                        ];
                        $post = $this->createPost($authorUser, $postType, $title, $persianLoremIpsum, $meta);
                        $post->media()->attach($sampleMedia->id);
                        $this->command->info($i);
                    }
                    break;

                // --- Services: Create 3 ---
                case 'services':
                    $serviceTitles = ['طراحی وبسایت', 'سئو و بهینه سازی', 'توسعه اپلیکیشن'];
                    foreach ($serviceTitles as $serviceTitle) {
                        $meta = [
                            'caption' => 'توضیح کوتاه درباره خدمات ' . $serviceTitle . '. ' . $persianLoremIpsum,
                        ];
                        $post = $this->createPost($authorUser, $postType, $serviceTitle, $longPersianLorem, $meta);
                        $post->media()->attach($sampleMedia->id);
                        $this->command->info($i);
                    }
                    break;

                // --- Statistics: Create 4 specific items ---
                case 'statistics':
                    $statNames = ['سفارشات مشتری ها', 'پروژه ها', 'مشتریان راضی', 'اعضای تیم'];
                    foreach ($statNames as $name) {
                        $meta = [
                            'amount' => $faker->numberBetween(40, 500),
                        ];
                        $post = $this->createPost($authorUser, $postType, $name, '', $meta);
                        $this->command->info($i);
                    }
                    break;

                // --- Recent Products: Create 6 ---
                case 'recent_products':
                    // ** این قسمت قبلاً 6 عدد ایجاد می‌کرد و همچنان 6 باقی می‌ماند. **
                    for ($i = 0; $i < 6; $i++) {
                        $title = 'پروژه ' . $faker->company();
                        $meta = [
                            'tags' => ['طراحی', 'توسعه'],
                        ];
                        $post = $this->createPost($authorUser, $postType, $title, $persianLoremIpsum, $meta);
                        $post->media()->attach($sampleMedia->id);
                        $this->command->info($i);

                    }
                    break;

                // --- Customer Reviews: Create 4 (Changed from 3 to 4) ---
                case 'customer_reviews':
                    // ** تعداد ایجاد شده به 4 تغییر یافت. **
                    for ($i = 0; $i < 4; $i++) {
                        $customerName = $faker->name();
                        $title = 'نظر ' . $customerName;
                        $meta = [
                            'comment' => 'نظر بسیار عالی درباره خدمات. ' . $persianLoremIpsum,
                            'customer_name' => $customerName,
                            'customer_name_caption' => $faker->jobTitle() . ' در ' . $faker->company(),
                        ];
                        $post = $this->createPost($authorUser, $postType, $title, '', $meta);
                        $post->media()->attach($sampleMedia->id);
                        $this->command->info($i);
                    }
                    break;

                // --- Articles: Create 3 with Category ---
                case 'articles':
                    for ($i = 0; $i < 3; $i++) {
                        $title = $persianTitles[array_rand($persianTitles)];
                        $meta = [
                            'caption' => 'خلاصه‌ای از مقاله: ' . $persianLoremIpsum,
                            'date' => verta()->subDays(rand(1, 30))->format('Y/m/d'),
                        ];
                        $post = $this->createPost($authorUser, $postType, $title, $longPersianLorem, $meta);

                        $post->media()->attach($sampleMedia->id);
                        if ($articleCategory) {
                            $post->categories()->attach($articleCategory->id);
                        }
                        $this->command->info($i);
                    }
                    break;

                default:
                    $title = $postType->title . ' آزمایشی';
                    $post = $this->createPost($authorUser, $postType, $title, $persianLoremIpsum, []);
                    $post->media()->attach($sampleMedia->id);
                    break;
            }
        }

        $this->command->info('ایجاد پست‌های آزمایشی با موفقیت انجام شد!');
    }

    /**
     * Helper function to create a post
     */
    private function createPost(User $user, PostType $postType, string $title, string $content, array $meta): Post
    {
        return Post::create([
            'title' => $title,
            'slug' => Str::slug($title) . '-' . rand(10000, 99999), // Use numbers for uniqueness
            'content' => $content,
            'is_published' => true,
            'status' => 'Approved',
            'published_at' => now()->subDays(rand(1, 30)),
            'author_id' => $user->id,
            'post_type_id' => $postType->id,
            'seen' => rand(10, 1000),
            'meta' => $meta,
            'lang' => 'fa',
        ]);
    }
}
