<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Get a list of all activity logs with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        // Start Query
        $query = Activity::query()
            ->with(['causer', 'subject']) // Eager load relationships to avoid N+1 queries
            ->latest(); // Show newest logs first

        // Filter: By User (Who did the action?)
        if ($request->has('causer_id')) {
            $query->where('causer_id', $request->input('causer_id'));
        }

        // Filter: By Subject Type (e.g., 'App\Models\Post')
        if ($request->has('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        // Filter: By Subject ID (e.g., Post #5)
        if ($request->has('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        // Filter: By Event (created, updated, deleted)
        if ($request->has('event')) {
            $query->where('event', $request->input('event'));
        }

        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Show a single log detail
     */
    public function show($id): JsonResponse
    {
        $log = Activity::with(['causer', 'subject'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * Clean up old logs (Optional utility)
     */
    public function destroy($id): JsonResponse
    {
        $log = Activity::findOrFail($id);
        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Log deleted successfully'
        ]);
    }
}
