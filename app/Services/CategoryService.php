<?php

namespace App\Services;

use App\Repositories\Categories\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function createCategory(array $data)
    {
        $data['creator_id'] = Auth::id();
        return DB::transaction(function () use ($data) {
            return $this->categoryRepository->create($data);
        });
    }

    public function deleteCategory($id)
    {
        return DB::transaction(function () use ($id) {
            return $this->categoryRepository->delete($id);
        });
    }
} 