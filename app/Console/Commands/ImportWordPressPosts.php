<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Models\Post;
use App\Models\PostType;
use App\Models\User;
use App\Models\Media;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImportWordPressPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:wordpress-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import posts and their media (with thumbnails) from a WordPress database';

    /**
     * The base URL for the WordPress uploads folder.
     *
     * @var string
     */
    private $wpUploadsBaseUrl = 'https://mttna.ir/wp-content/uploads/';

    /**
     * The desired thumbnail dimensions.
     *
     * @var array
     */
    private const THUMBNAIL_DIMENSIONS = ['width' => 402, 'height' => 204];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (empty(config('app.url')) || config('app.url') === 'http://localhost') {
            $this->error('Your APP_URL is not set correctly in your .env file. Please set it to the full public URL of your application.');
            return 1;
        }

        $this->info('Starting WordPress posts and media import...');

        $newsPostType = PostType::where('name', 'news')->first();
        if (!$newsPostType) {
            $this->error('PostType "news" not found. Please seed your post types first.');
            return 1;
        }

        $author = User::first();
        if (!$author) {
            $this->error('No users found in your Laravel database. Please create a default user.');
            return 1;
        }

        $wpPosts = DB::connection('mttna_wp')
            ->table('posts')
            ->where('post_type', 'post')
            ->where('post_status', 'publish')
            ->get();
            
        if ($wpPosts->isEmpty()) {
            $this->warn('No posts found in the WordPress database to import.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($wpPosts->count());
        $progressBar->start();

        foreach ($wpPosts as $wpPost) {
            $processedData = $this->processContentAndMedia($wpPost->post_content, $author->id);

            $postData = [
                'title'        => $wpPost->post_title,
                'slug'         => urldecode($wpPost->post_name),
                'content'      => $processedData['new_content'],
                'is_published' => true,
                'status'       => 'Approved',
                'published_at' => $wpPost->post_date,
                'author_id'    => $author->id,
                'post_type_id' => $newsPostType->id,
                'seen'         => 0,
                'meta'         => [
                    'summary' => $wpPost->post_excerpt,
                    'wp_original_id' => $wpPost->ID,
                ],
                'lang'         => 'fa',
            ];
            
            $laravelPost = Post::updateOrCreate(
                ['meta->wp_original_id' => $wpPost->ID],
                $postData
            );

            // Attach all collected media (originals and thumbnails) to the post
            if (!empty($processedData['media_ids'])) {
                $laravelPost->media()->sync($processedData['media_ids']);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nImport completed successfully!");

        return 0;
    }

    private function processContentAndMedia(string $content, int $uploaderId): array
    {
        $mediaIds = [];
        $pattern = '/(src|href)="([^"]*?\/wp-content\/uploads\/[^"]+)"/i';

        $newContent = preg_replace_callback($pattern, function ($matches) use (&$mediaIds, $uploaderId) {
            $attribute = $matches[1];
            $oldUrl = $matches[2];

            $relativePath = substr($oldUrl, strpos($oldUrl, 'wp-content/uploads/') + strlen('wp-content/uploads/'));
            $fileName = basename($relativePath);
            $localDirectory = public_path('storage/media');
            $localFilePath = $localDirectory . '/' . $fileName;

            // Create directories if they don't exist
            if (!File::exists($localDirectory)) File::makeDirectory($localDirectory, 0755, true);
            if (!File::exists($localDirectory . '/thumbnails')) File::makeDirectory($localDirectory . '/thumbnails', 0755, true);
            
            // Download the file if it's not already here
            if (!File::exists($localFilePath)) {
                try {
                    $response = Http::withoutVerifying()->get($this->wpUploadsBaseUrl . $relativePath);
                    if ($response->successful()) {
                        File::put($localFilePath, $response->body());
                        $this->line("\nDownloaded: " . $fileName);
                    } else {
                        $this->warn("\nFailed to download: " . $oldUrl . " (Status: " . $response->status() . ")");
                        return $matches[0];
                    }
                } catch (ConnectionException $e) {
                    $this->error("\nConnection error while downloading " . $oldUrl . ": " . $e->getMessage());
                    return $matches[0];
                }
            }

            // --- STEP 1: Create the 'original' media record ---
            $originalMediaPathForDb = 'media/' . $fileName;
            $originalMedia = Media::firstOrCreate(
                ['file_path' => $originalMediaPathForDb],
                [
                    'file_type' => File::mimeType($localFilePath),
                    'uploader_id' => $uploaderId,
                    'alt_text' => pathinfo($fileName, PATHINFO_FILENAME),
                    'type' => 'original', // This is the full-size image
                ]
            );

            // Add its ID to the list for syncing
            if (!in_array($originalMedia->id, $mediaIds)) {
                $mediaIds[] = $originalMedia->id;
            }

            // --- STEP 2: Create the 'thumbnail' media record if it's an image ---
            $fileIsImage = Str::startsWith($originalMedia->file_type, 'image/');
            if ($fileIsImage) {
                $thumbnailMedia = $this->createAndStoreThumbnail($localFilePath, $uploaderId);
                // Add the thumbnail's ID to the list for syncing
                if ($thumbnailMedia && !in_array($thumbnailMedia->id, $mediaIds)) {
                    $mediaIds[] = $thumbnailMedia->id;
                }
            }
            
            // Replace URL in content. It should always point to the original, full-size image.
            $newUrl = rtrim(config('app.url'), '/') . '/storage/' . $originalMediaPathForDb;
            return $attribute . '="' . $newUrl . '"';
        }, $content);

        return [
            'new_content' => $newContent,
            'media_ids' => array_unique($mediaIds),
        ];
    }

    private function createAndStoreThumbnail(string $originalPath, int $uploaderId): ?Media
    {
        try {
            $originalFileName = basename($originalPath);
            $thumbnailFileName = 'thumb_' . $originalFileName;
            $thumbnailPathForDb = 'media/thumbnails/' . $thumbnailFileName;
            $thumbnailFullPath = public_path('storage/' . $thumbnailPathForDb);

            // Check if the thumbnail media record already exists. If so, return it.
            $existingThumbnail = Media::where('file_path', $thumbnailPathForDb)->first();
            if ($existingThumbnail) {
                return $existingThumbnail;
            }

            // If the physical thumbnail file doesn't exist, create it.
            if (!File::exists($thumbnailFullPath)) {
                $image = Image::read($originalPath);
                $image->resize(self::THUMBNAIL_DIMENSIONS['width'], self::THUMBNAIL_DIMENSIONS['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $image->save($thumbnailFullPath);
                $this->line("\nCreated thumbnail: " . $thumbnailFileName);
            }

            // Create the media record for the thumbnail
            return Media::create([
                'file_path' => $thumbnailPathForDb,
                'file_type' => File::mimeType($thumbnailFullPath),
                'alt_text' => pathinfo($originalFileName, PATHINFO_FILENAME),
                'caption' => '(thumbnail)',
                'uploader_id' => $uploaderId,
                'type' => 'thumbnail', // This is the resized image
            ]);

        } catch (\Exception $e) {
            $this->error("\nFailed to create thumbnail for " . basename($originalPath) . ": " . $e->getMessage());
            return null;
        }
    }
}