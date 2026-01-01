<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\Roles\StoreRequest;
use App\Http\Requests\Roles\UpdateRequest;
use App\Repositories\Roles\RoleRepositoryInterface;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RolesController extends Controller
{
    protected RoleRepositoryInterface $roleRepository;
    protected RoleService $roleService;

    public function __construct(RoleRepositoryInterface $roleRepository, RoleService $roleService)
    {
        $this->roleRepository = $roleRepository;
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $roles = $this->roleRepository->all($perPage, $request->only(['s']));
        return response()->json(['success' => true, 'data' => $roles]);
    }

    /**
     * Display the specified role.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $role = $this->roleRepository->find($id);
            $role->load('permissions');
            return response()->json(['success' => true, 'data' => $role]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نقش یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Role show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت نقش.'
            ], 500);
        }
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        try {
            $role = $this->roleService->createRole($validated);
            return response()->json(['success' => true, 'data' => $role], 201);
        } catch (\Exception $e) {
            Log::error('Role store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد نقش. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        $validated = $request->validated();
        try {
            $role = $this->roleService->updateRole($id, $validated);
            return response()->json(['success' => true, 'data' => $role]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نقش یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Role update failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'role_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش نقش. لطفا دوباره تلاش کنید',
            ], 500);
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $this->roleService->deleteRole($id);
            return response()->json(['success' => true, 'message' => 'نقش با موفقیت حذف شد.']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نقش یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Role delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'role_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف نقش',
            ], 500);
        }
    }
}
