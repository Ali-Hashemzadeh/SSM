<?php

namespace App\Repositories\Pages;

interface PageRepositoryInterface
{
    public function all($perPage = 15, array $relations = [], array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function findById($id);
} 