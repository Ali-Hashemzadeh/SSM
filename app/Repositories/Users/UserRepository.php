<?php

namespace App\Repositories\Users;

use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = User::query();
        // Search by mobile
        if (!empty($filters['mobile'])) {
            $query->where('mobile', 'like', "%{$filters['mobile']}%");
        }
        // Search by full name (first_name + ' ' + last_name)
        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
        }
        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (!empty($filters['creator_id'])) {
            $query->where('creator_id', $filters['creator_id']);
        }
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return User::findOrFail($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return true;
    }
} 