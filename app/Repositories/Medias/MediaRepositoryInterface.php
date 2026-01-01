<?php

namespace App\Repositories\Medias;

interface MediaRepositoryInterface
{
    public function all($perPage = 15, array $filters = []);
    public function find($id);
    public function create(array $data);
    public function delete($id);
} 