<?php

namespace App\Services;

use App\Repositories\Tags\TagRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TagService
{
    protected TagRepositoryInterface $tagRepository;

    public function __construct(TagRepositoryInterface $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function createTag(array $data)
    {
        $data['creator_id'] = Auth::id();
        return DB::transaction(function () use ($data) {
            return $this->tagRepository->create($data);
        });
    }

    public function deleteTag($id)
    {
        return DB::transaction(function () use ($id) {
            return $this->tagRepository->delete($id);
        });
    }
} 