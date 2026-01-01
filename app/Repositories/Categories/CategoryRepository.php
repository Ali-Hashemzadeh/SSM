<?php

namespace App\Repositories\Categories;

use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Category::query();
        // Search by name or slug
        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('slug', 'like', "%$search%" );
            });
        }
        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['post_type_id']) && $filters['post_type_id'] != 'undefined' && $filters['post_type_id'] != 'null') {
            $query->where('post_type_id', $filters['post_type_id']);
        }
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Category::findOrFail($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return true;
    }
} 