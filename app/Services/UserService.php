<?php

namespace App\Services;

use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected FileUploadService $fileUploadService;

    public function __construct(UserRepositoryInterface $userRepository, FileUploadService $fileUploadService)
    {
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            // $data['password'] = Hash::make($data['password']);
            $profilePicture = request()->file('profile_picture');
            if ($profilePicture) {
                $data['profile_picture'] = $this->fileUploadService->upload($profilePicture, 'profile_pictures');
            }
            if (Auth::check()) {
                $data['creator_id'] = Auth::id();
            }
            return $this->userRepository->create($data);
        });
    }

    public function updateUser($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            $profilePicture = request()->file('profile_picture');
            if ($profilePicture) {
                $data['profile_picture'] = $this->fileUploadService->upload($profilePicture, 'profile_pictures');
            }
            return $this->userRepository->update($id, $data);
        });
    }
}
