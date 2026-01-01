<?php

namespace App\Repositories\Posts;

interface PostRepositoryInterface
{
    public function all($perPage = 15, array $relations = [], array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function incrementSeen($id);
    public function getRecentByCategoryName($name, $limit = 4);
    public function getRecentByPostTypeName($type, $limit = 5);
    public function getRecentByPostTypeAndCategory($type, $categorySlug, $limit = 5);
} 