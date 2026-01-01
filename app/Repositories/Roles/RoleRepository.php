<?php

namespace App\Repositories\Roles;

use App\Models\Role;

class RoleRepository implements RoleRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Role::query();
        // Search by name or slug
        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('slug', 'like', "%$search%" );
            });
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
        return Role::findOrFail($id);
    }

    public function create(array $data)
    {
        return Role::create($data);
    }

    public function update($id, array $data)
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role;
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return true;
    }
} 