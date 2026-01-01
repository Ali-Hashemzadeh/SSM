<?php

namespace App\Repositories\Comments;

interface CommentRepositoryInterface
{
    public function all($perPage = 15, array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
} 