<?php

namespace App\Repositories\Menus;

interface MenuRepositoryInterface
{
    public function all($perPage = 15, array $relations = [], array $filters = [], bool $paginate = true);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
} 