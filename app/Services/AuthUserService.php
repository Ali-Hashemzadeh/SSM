<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthUserService
{
    /**
     * Find a user by their mobile or email.
     */
    public function findUserByRecipient(string $recipient, string $type): ?User
    {
        return User::where($type, $recipient)->first();
    }

    /**
     * Create a new user from the public registration form.
     */
    public function registerNewUser(array $data): User
    {
        // Find the default 'user' role
        $userRole = Role::where('slug', 'user')->firstOrFail();

        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['phone'] ?? null, // Map 'phone' to 'mobile'
            'email' => $data['email'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'country' => $data['country'],
            'province' => $data['province'] ?? null,
            'role_id' => $userRole->id,
            'password' => Hash::make(Str::random(20)), // Create a secure, random password
        ]);
    }
}
