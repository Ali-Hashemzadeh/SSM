<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles with slug and Persian names
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'سوپر ادمین', 'description' => 'دسترسی کامل به تمام بخش‌های سیستم']
        );

        // Get all permissions
        $permissions = Permission::all()->keyBy('name');

        // Super Admin - All permissions
        $superAdmin->permissions()->sync($permissions->pluck('id'));

        // Assign dashboard permissions to user role
                
        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'کاربر', 'description' => 'نقش کاربر عادی سیستم']
        );
        
        $dashboardPermissions = Permission::whereIn('name', ['dashboard.view', 'dashboard.analytics'])->pluck('id');
        $userRole->permissions()->syncWithoutDetaching($dashboardPermissions);
    }
} 