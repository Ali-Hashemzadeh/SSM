<?php

namespace App\Repositories\Menus;

use App\Models\Menu;

class MenuRepository implements MenuRepositoryInterface
{

    public function all($perPage = 15, array $relations = [], array $filters = [], bool $paginate = true)
    {
        $filters = array_filter($filters, fn($v)=>$v!==null && $v!=='');
        $query = Menu::query();
        if($relations) $query->with($relations);

        if(!empty($filters['s'])){
            $search = $filters['s'];
            $query->where('title','like',"%$search%");
        }
        if(isset($filters['parent_id'])){
            $query->where('parent_id',$filters['parent_id']);
        }
        if(isset($filters['lang'])){
            $query->where('lang',$filters['lang']);
        }

        $query->orderBy('order');

        if ($paginate) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    public function find($id)
    {
        return Menu::findOrFail($id);
    }

    public function create(array $data)
    {
        return Menu::create($data);
    }

    public function update($id, array $data)
    {
        $menu = Menu::findOrFail($id);
        $menu->update($data);
        return $menu;
    }

    public function delete($id)
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();
        return true;
    }
} 