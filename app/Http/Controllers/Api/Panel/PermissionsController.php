<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionsController extends Controller
{
    /**
     * Display a listing of the permissions.
     */
    public function index(): JsonResponse
    {
        $permissions = Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }
}
