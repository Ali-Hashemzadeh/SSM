<?php

namespace App\Repositories\Users;

interface UserRepositoryInterface
{
    public function all($perPage = 15, array $filters = []);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
} 