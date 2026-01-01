<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Services\FileUploadService;
use App\Repositories\Users\UserRepositoryInterface;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UsersController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected FileUploadService $fileUploadService;
    protected UserService $userService;

    public function __construct(UserRepositoryInterface $userRepository, FileUploadService $fileUploadService, UserService $userService)
    {
        $this->userRepository = $userRepository;
        $this->fileUploadService = $fileUploadService;
        $this->userService = $userService;
    }

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = $this->userRepository->all($perPage, $request->only(['mobile', 's']));
        return response()->json(['success' => true, 'data' => $users]);
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $this->userRepository->find($id);
            return response()->json(['success' => true, 'data' => $user]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('User show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت کاربر.'
            ], 500);
        }
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $this->userService->createUser($validated);
            return response()->json(['success' => true, 'data' => $user], 201);
        } catch (\Exception $e) {
            Log::error('User store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد کاربر. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $user = $this->userService->updateUser($id, $validated);
            return response()->json(['success' => true, 'data' => $user]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش کاربر. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $this->userRepository->delete($id);
            return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'کاربر یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('User delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف کاربر.'
            ], 500);
        }
    }

    /**
     * Get users created by the authenticated user.
     */
    public function my(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = $this->userRepository->all($perPage, array_merge($request->only(['mobile', 's']), [
            'creator_id' => $request->user()->id
        ]));
        return response()->json(['success' => true, 'data' => $users]);
    }
}
