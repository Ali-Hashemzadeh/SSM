<?php

namespace App\Repositories\Pages;

use App\Enums\PageStatuses;
use App\Models\Page;
use App\Models\PageTranslation;
use Illuminate\Support\Facades\DB;

class PageRepository implements PageRepositoryInterface
{
    public function all($perPage = 15, array $relations = [], array $filters = [])
    {
        $filters = array_filter($filters, fn($v) => $v !== null && $v !== '');
        $query = Page::query();
        if ($relations) $query->with($relations);

        // Search by title or slug
        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->where(function($q) use ($search) {
                $q->where('title','like',"%$search%")
                  ->orWhere('slug','like',"%$search%");
            });
        }
        // Filter by status, is_published, author_id
        if (isset($filters['status'])) $query->where('status',$filters['status']);
        if (isset($filters['is_published'])) $query->where('is_published',$filters['is_published']);
        if (isset($filters['author_id'])) $query->where('author_id',$filters['author_id']);
        // Date range
        if (!empty($filters['date_from'])) $query->whereDate('created_at','>=',$filters['date_from']);
        if (!empty($filters['date_to'])) $query->whereDate('created_at','<=',$filters['date_to']);

        return $query->paginate($perPage);
    }

    public function find($slug)
    {
        return Page::where('slug', $slug)->first();
    }

    public function findById($id){
        return Page::find($id);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $page = Page::create([
                'slug' => $data['slug'],
                'author_id' => $data['author_id'],
            ]);

            foreach ($data['translations'] as $lang => $translationData) {
                PageTranslation::create([
                    'page_id' => $page->id,
                    'lang' => $lang,
                    'title' => $translationData['title'],
                    'content' => $translationData['content'],
                ]);
            }
            return $page->load('translations');
        });
    }

    public function update($id, array $data)
    {
        $page = Page::findOrFail($id);
        return DB::transaction(function () use ($page, $data) {
            // Update the page's core fields (e.g., slug, status, author_id, etc.)
            $page->update([
                'slug'   => $data['slug'],
                'status' => $data['status'] ?? array_search($page->status, PageStatuses::pairs()),
                'author_id' => $data['author_id'] ?? $page->author_id,
            ]);
            // Update or create translation records
            foreach ($data['translations'] as $lang => $translationData) {
                $page->translations()->updateOrCreate(
                    ['lang' => $lang],
                    [
                        'title'   => $translationData['title'],
                        'content' => $translationData['content'] ?? null,
                    ]
                );
            }

            // Handle media sync
            if (isset($data['media'])) {
                $page->media()->syncWithPivotValues($data['media'], ['display_order' => 1]);
            }

            return $page->load(['translations', 'media', 'author']);
        });
    }

    public function delete($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();
        return true;
    }
} 