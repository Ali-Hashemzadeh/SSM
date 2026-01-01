<?php

namespace Database\Seeders;

use App\Models\Slider;
use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Seeder;

class SliderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a user for media uploader if it doesn't exist
        $user = User::firstOrCreate(
            ['mobile' => '09197238119'],
            [
                'first_name' => 'علی',
                'last_name' => 'هاشم زاده',
                'password' => bcrypt('password123'),
                'role_id' => 1,
                'creator_id' => 1,
            ]
        );
        $user2 = User::create(
            [
                'first_name' => 'مهسا',
                'last_name' => 'حاج علی گل',
                'mobile' => '09120521497',
                'password' => bcrypt('password123'),
                'role_id' => 1,
                'creator_id' => 1,
            ]
        );

        // Create one slider
        $slider = Slider::create([
            'status' => true,
        ]);

        // Create four media items for the slider
        $mediaItems = [
            [
                'file_path' => 'sample/sample.jpg',
                'file_type' => 'image/jpeg',
                'alt_text' => 'اسلاید اول - تصویر نمونه',
                'caption' => 'اسلاید اول با تصویر نمونه',
                'uploader_id' => $user->id,
            ],
            [
                'file_path' => 'sample/sample.jpg',
                'file_type' => 'image/jpeg',
                'alt_text' => 'اسلاید دوم - تصویر نمونه',
                'caption' => 'اسلاید دوم با تصویر نمونه',
                'uploader_id' => $user->id,
            ],
            [
                'file_path' => 'sample/sample.jpg',
                'file_type' => 'image/jpeg',
                'alt_text' => 'اسلاید سوم - تصویر نمونه',
                'caption' => 'اسلاید سوم با تصویر نمونه',
                'uploader_id' => $user->id,
            ],
            [
                'file_path' => 'sample/sample.jpg',
                'file_type' => 'image/jpeg',
                'alt_text' => 'اسلاید چهارم - تصویر نمونه',
                'caption' => 'اسلاید چهارم با تصویر نمونه',
                'uploader_id' => $user->id,
            ],
        ];

        // Create media records and attach them to the slider
        foreach ($mediaItems as $index => $mediaData) {
            $media = Media::create($mediaData);

            // Attach media to slider with display order
            $slider->media()->attach($media->id, [
                'display_order' => $index + 1,
            ]);
        }
    }
}
