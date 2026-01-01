<?php

namespace App\Repositories\Tags;

use App\Models\Tag;

class TagRepository implements TagRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Tag::query();
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
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Tag::findOrFail($id);
    }

    public function create(array $data)
    {
        return Tag::create($data);
    }

    public function update($id, array $data)
    {
        $tag = Tag::findOrFail($id);
        $tag->update($data);
        return $tag;
    }

    public function delete($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->delete();
        return true;
    }
} 