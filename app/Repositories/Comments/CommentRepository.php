<?php

namespace App\Repositories\Comments;

use App\Models\Comment;

class CommentRepository implements CommentRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Comment::query();
        // Filter by status
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
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
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Comment::findOrFail($id);
    }

    public function create(array $data)
    {
        $data['post_id'] = request()->id;
        return Comment::create($data);
    }

    public function update($id, array $data)
    {
        $comment = Comment::findOrFail($id);
        $comment->update($data);
        return $comment;
    }

    public function delete($id)
    {
        $comment = Comment::findOrFail($id);
        $comment->delete();
        return true;
    }
} 