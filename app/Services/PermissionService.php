<?php

namespace App\Services;

use App\Models\User;

class PermissionService
{
    public function hasPermission(User $user, string $permission): bool
    {
        $role = $user->role;
        if (!$role) {
            return false;
        }
        return $role->permissions()->where('name', $permission)->exists();
    }

    public function hasAnyPermission(User $user, $permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = explode('|', $permissions);
        }
        $role = $user->role;
        if (!$role) {
            return false;
        }
        return $role->permissions()->whereIn('name', $permissions)->exists();
    }
} 