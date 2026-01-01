<?php

namespace App\Services;

use App\Repositories\Comments\CommentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommentService
{
    protected CommentRepositoryInterface $commentRepository;

    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function createComment(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['author_id'] = Auth::id();
            return $this->commentRepository->create($data);
        });
    }

    public function updateStatus($id, $status)
    {
        return DB::transaction(function () use ($id, $status) {
            $comment = $this->commentRepository->find($id);
            if (!$comment) {
                return null;
            }
            $comment->status = $status;
            $comment->save();
            return $comment;
        });
    }

    public function deleteComment($id)
    {
        return DB::transaction(function () use ($id) {
            $comment = $this->commentRepository->find($id);
            if (!$comment) {
                return false;
            }
            $comment->delete();
            return true;
        });
    }

    public function getUserComments($perPage = 15, array $filters = [])
    {
        return $this->commentRepository->all($perPage, $filters);
    }
} 