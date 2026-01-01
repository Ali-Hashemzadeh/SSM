<?php

namespace App\Services;

use App\Repositories\Menus\MenuRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MenuService
{
    public function __construct(private MenuRepositoryInterface $menuRepository)
    {
    }

    public function createMenu(array $data)
    {
        return DB::transaction(fn()=> $this->menuRepository->create($data));
    }

    public function updateMenu($id,array $data)
    {
        return DB::transaction(fn()=> $this->menuRepository->update($id,$data));
    }

    public function deleteMenu($id)
    {
        return DB::transaction(fn()=> $this->menuRepository->delete($id));
    }
} 