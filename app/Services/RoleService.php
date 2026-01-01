<?php

namespace App\Services;

use App\Repositories\Roles\RoleRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function createRole(array $data)
    {
        $data['creator_id'] = Auth::id();
        return DB::transaction(function () use ($data) {
            $role = $this->roleRepository->create($data);
            if (isset($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }
            $role->load('permissions');
            return $role;
        });
    }

    public function updateRole($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $role = $this->roleRepository->update($id, $data);
            if (isset($data['permissions'])) {
                $role->permissions()->sync($data['permissions']);
            }
            $role->load('permissions');
            return $role;
        });
    }

    public function deleteRole($id)
    {
        return DB::transaction(function () use ($id) {
            return $this->roleRepository->delete($id);
        });
    }
} 