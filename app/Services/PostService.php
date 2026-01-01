<?php

namespace App\Services;

use App\Repositories\Posts\PostRepositoryInterface;
use App\Models\PostType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PostService
{
    protected PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function createPost(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Validate meta data based on post type
            // $validationErrors = $this->validateMetaData($data);
            // if ($validationErrors) {
            //     return ['errors' => $validationErrors];
            // }
            
            $data['author_id'] = Auth::id();
            $post = $this->postRepository->create($data);
            $this->syncRelations($post, $data);
            return $post->load(['author', 'categories', 'tags', 'media', 'postType']);
        });
    }

    public function updatePost($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $post = $this->postRepository->find($id);
            
            // Validate meta data based on post type
            // $validationErrors = $this->validateMetaData($data, $post->post_type_id);
            // if ($validationErrors) {
            //     return ['errors' => $validationErrors];
            // }
            
            $post->update($data);
            $this->syncRelations($post, $data);
            return $post->load(['author', 'categories', 'tags', 'media', 'postType']);
        });
    }

    public function deletePost($id)
    {
        return DB::transaction(function () use ($id) {
            $post = $this->postRepository->find($id);
            $post->delete();
            return true;
        });
    }

    public function publishPost($id)
    {
        return DB::transaction(function () use ($id) {
            $post = $this->postRepository->find($id);
            $post->is_published = !$post->is_published;
            $post->published_at = now();
            $post->save();
            return $post;
        });
    }

    public function updateStatus($id, $status)
    {
        return DB::transaction(function () use ($id, $status) {
            $post = $this->postRepository->find($id);
            $post->status = $status;
            $post->save();
            return $post;
        });
    }

    public function getRecentByCategorySlug($name, $limit = 4)
    {
        return $this->postRepository->getRecentByCategoryName($name, $limit);
    }

    public function getRecentByPostType($type, $limit = 5)
    {
        return $this->postRepository->getRecentByPostTypeName($type, $limit);
    }

    public function getRecentByPostTypeAndCategory($type, $categorySlug, $limit = 5)
    {
        return $this->postRepository->getRecentByPostTypeAndCategory($type, $categorySlug, $limit);
    }

    protected function syncRelations($post, $data)
    {
        if (isset($data['categories'])) {
            $post->categories()->sync($data['categories']);
        }
        if (isset($data['tags'])) {
            $post->tags()->sync($data['tags']);
        }
        if (isset($data['media'])) {
            $media = collect($data['media'])->mapWithKeys(fn($id, $i) => [$id => ['display_order' => $i+1]]);
            $post->media()->sync($media);
        }
    }

    protected function validateMetaData(array $data, $postTypeId = null)
    {
        // Get post type ID from data or use provided one
        $postTypeId = $postTypeId ?? $data['post_type_id'] ?? null;

        // Get post type and its fields
        $postType = PostType::find($postTypeId);

        $fields = $postType->fields;
        $meta = $data['meta'] ?? [];
        // Skip validation if meta is empty (for updates)
        if (empty($meta)) {
            return null;
        }

        // Build validation rules based on post type fields
        $rules = $this->buildMetaValidationRules($fields);
        
        // Validate meta data
        $validator = Validator::make($meta, $rules, $this->getMetaValidationMessages($postType));
        
        if ($validator->fails()) {
            return $validator->errors();
        }

        return null;
    }

    protected function getMetaValidationMessages(PostType $postType): array
    {
        $messages = [];
        $fields = $postType->fields;

        foreach ($fields as $fieldName => $fieldConfig) {
            if (is_string($fieldConfig)) {
                $messages[$fieldConfig . '.required'] = "فیلد {$fieldConfig} برای نوع پست {$postType->title} الزامی است.";
                $messages[$fieldConfig . '.string'] = "فیلد {$fieldConfig} باید متن باشد.";
            } elseif (is_array($fieldConfig)) {
                $messages[$fieldName . '.required'] = "فیلد {$fieldName} برای نوع پست {$postType->title} الزامی است.";
                $messages[$fieldName . '.array'] = "فیلد {$fieldName} باید آرایه باشد.";
                
                foreach ($fieldConfig as $nestedField) {
                    $messages["{$fieldName}.{$nestedField}.required"] = "فیلد {$nestedField} در {$fieldName} الزامی است.";
                    $messages["{$fieldName}.{$nestedField}.string"] = "فیلد {$nestedField} در {$fieldName} باید متن باشد.";
                }
            }
        }

        return $messages;
    }

    public function getMetaValidationRules($postTypeId): array
    {
        $postType = PostType::find($postTypeId);
        if (!$postType) {
            return [];
        }

        return $this->buildMetaValidationRules($postType->fields);
    }

    public function getMetaValidationMessagesForPostType($postTypeId): array
    {
        $postType = PostType::find($postTypeId);
        if (!$postType) {
            return [];
        }

        return $this->getMetaValidationMessages($postType);
    }

    protected function buildMetaValidationRules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $fieldName => $fieldConfig) {
            if (is_string($fieldConfig)) {
                // Simple field (like 'summary', 'date', 'location', etc.)
                $rules[$fieldConfig] = 'required|string';
            } elseif (is_array($fieldConfig)) {
                // Nested field structure (like 'eduacational_background', 'publications', etc.)
                $rules[$fieldName] = 'required|array';
                foreach ($fieldConfig as $nestedField) {
                    $rules["$fieldName.$nestedField"] = 'required|string';
                }
            }
        }

        return $rules;
    }
} 