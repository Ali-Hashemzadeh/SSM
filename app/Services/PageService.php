<?php

namespace App\Services;

use App\Repositories\Pages\PageRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageService
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    public function createPage(array $data)
    {
        return DB::transaction(function() use ($data) {
            $data['author_id'] = Auth::id();
            $page = $this->pageRepository->create($data);
            $this->syncRelations($page, $data);
            return $page->load(['author', 'media']);
        });
    }

    public function updatePage($id, array $data)
    {
        return DB::transaction(function() use ($id,$data){
            $data['author_id'] = Auth::id();
            $page = $this->pageRepository->update($id, $data);
            $this->syncRelations($page, $data);
            return $page->load(['author', 'media']);
        });
    }

    public function deletePage($id)
    {
        return DB::transaction(function() use ($id){
            return $this->pageRepository->delete($id);
        });
    }

    public function togglePublish($id)
    {
        return DB::transaction(function() use ($id){
            $page = $this->pageRepository->findById($id);
            $page->is_published = !$page->is_published;
            $page->published_at = now();
            $page->save();
            return $page;
        });
    }

    /**
     * Update page status (Pending / Approved / Rejected).
     */
    public function updateStatus($id, string $status)
    {
        return DB::transaction(function() use ($id,$status){
            $page = $this->pageRepository->findById($id);
            $page->status = $status;
            $page->save();
            return $page;
        });
    }

    /**
     * Sync page relations like media.
     */
    protected function syncRelations($page, array $data): void
    {
        if (isset($data['media'])) {
            $media = collect($data['media'])->mapWithKeys(fn($id, $i) => [$id => ['display_order' => $i + 1]]);
            $page->media()->sync($media);
        }
    }
} 