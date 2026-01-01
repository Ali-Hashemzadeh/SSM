<?php

namespace App\Repositories\Posts;

use App\Models\Post;
use App\Repositories\Posts\PostRepositoryInterface;

class PostRepository implements PostRepositoryInterface
{
    public function all($perPage = 15, array $relations = [], array $filters = [])
    {
        $lang = app()->getLocale() ?: 'fa';
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Post::query();
        if (!empty($relations)) {
            $query->with($relations);
        }
        $query->where('lang', $lang);
        $query->orderBy('id', 'desc');
        // Search by title or slug
        if (!empty($filters['s']) && $filters['s'] !== "undefined") {
            $search = $filters['s'];
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('slug', 'like', "%$search%" );
            });
        }
        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        // Filter by is_published
        if (isset($filters['is_published'])) {
            $query->where('is_published', $filters['is_published']);
        }
        // Filter by author_id
        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }
        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        // Filter by post_type_id
        if (isset($filters['post_type_id'])) {
            $query->where('post_type_id', $filters['post_type_id']);
        }
        // Filter by post type name
        if (!empty($filters['post_type_name'])) {
            $query->whereHas('postType', function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['post_type_name']}%");
            });
        }

        // Filter by lang
        if (!empty($filters['lang'])) {
            $query->where('lang', $filters['lang']);
        }
        if (!empty($filters['category_slug']) && $filters['category_slug'] !== "null" && $filters['category_slug'] !== "undefined") {
            $query->whereHas('categories', function($q) use ($filters) {
                $q->where('slug', 'like', "%{$filters['category_slug']}%");
            });
        }

        // Order by seen count (asc or desc)
        if (!empty($filters['seen_order'])) {
            $query->orderBy('seen', $filters['seen_order']);
        }

        // Order by date (newest or oldest)
        if (!empty($filters['order'])) {
            $query->orderBy('id', $filters['order']);
        }
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Post::findOrFail($id);
    }

    public function create(array $data)
    {
        return Post::create($data);
    }

    public function update($id, array $data)
    {
        $post = Post::findOrFail($id);
        $post->update($data);
        return $post;
    }

    public function delete($id)
    {
        $post = Post::findOrFail($id);
        $post->delete();
        return true;
    }

    public function incrementSeen($id)
    {
        $post = Post::findOrFail($id);
        $post->increment('seen');
        return $post;
    }

    public function getRecentByCategoryName($name, $limit = 4)
    {
        return Post::whereHas('categories', function ($q) use ($name) {
            $q->where('name', $name);
        })->latest()->take($limit)->get();
    }

    public function getRecentByPostTypeName($type, $limit = 5)
    {
        return Post::with('media')->whereHas('postType', function ($q) use ($type) {
            $q->where('name', $type)->where('lang', app()->getLocale());
        })->where('is_published', 1)->latest()->take($limit)->get();
    }

    public function getRecentByPostTypeAndCategory($type, $categorySlug, $limit = 5)
    {
        return Post::with('media')->whereHas('postType', function ($q) use ($type) {
            $q->where('name', $type);
        })->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        })->where('is_published', 1)->latest()->take($limit)->get();
    }
}
