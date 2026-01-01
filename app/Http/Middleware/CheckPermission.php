<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\PermissionService;

class CheckPermission
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function handle(Request $request, Closure $next, $permissions)
    {
        if (env('CHECK_PERMISSION', true) === false || env('CHECK_PERMISSION', true) === 'false') {
            return $next($request);
        }
        $user = $request->user();
        $permissionsArr = explode('|', $permissions);
        $globalPermission = $permissionsArr[0];
        $selfPermission = $permissionsArr[1] ?? null;

        // Global permission check
        if ($user && $this->permissionService->hasPermission($user, $globalPermission)) {
            return $next($request);
        }

        // Self permission check
        if ($selfPermission && $user && $this->permissionService->hasPermission($user, $selfPermission)) {
            // Try to get the resource from the route (e.g., post, comment, media, category, tag)
            $resource = $request->route('post') ?? $request->route('comment') ?? $request->route('media') ?? $request->route('user');
            if ($resource && (
                (isset($resource->author_id) && $resource->author_id == $user->id) ||
                (isset($resource->uploader_id) && $resource->uploader_id == $user->id) ||
                (isset($resource->creator_id) && $resource->creator_id == $user->id)
            )) {
                return $next($request);
            }
        }

        return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }
} 