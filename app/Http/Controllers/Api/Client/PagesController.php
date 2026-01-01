<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Pages\PageRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Page;

class PagesController extends Controller
{
    public function __construct(private PageRepositoryInterface $pageRepository)
    {
    }

    /**
     * List published pages with optional search & date filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['s', 'date_from', 'date_to']);
        $filters['is_published'] = 1; // only published pages
        $filters['status'] = 'Approved'; // only approved pages
        $perPage = $request->get('per_page', 15);
        $pages = $this->pageRepository->all($perPage, ['author', 'media'], $filters);
        return response()->json(['success' => true, 'data' => $pages]);
    }

    /**
     * Show a single published page by ID or slug.
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $query = Page::where('is_published', 1)->where('status', 'Approved');
            $page = $query->where('slug', $slug)->firstOrFail();
            $page->load(['author', 'media', 'translations']);
            return response()->json(['success' => true, 'data' => $page]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'صفحه یافت نشد'], 404);
        }
    }
}
