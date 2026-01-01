<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Posts\StoreRequest;
use App\Http\Requests\Posts\UpdateRequest;
use Illuminate\Support\Facades\Log;
use App\Repositories\Posts\PostRepositoryInterface;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Services\CommentService;

class PostsController extends Controller
{
    protected PostRepositoryInterface $postRepository;
    protected PostService $postService;
    protected CommentService $commentService;

    public function __construct(PostRepositoryInterface $postRepository, PostService $postService, CommentService $commentService)
    {
        $this->postRepository = $postRepository;
        $this->postService = $postService;
        $this->commentService = $commentService;
    }

    /**
     * Display a list of the current user's posts
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['s', 'status', 'is_published', 'date_from', 'date_to', 'author_id', 'post_type_id', 'post_type_name', 'seen', 'category_slug', 'lang']);
        $filters['author_id'] = Auth::id();
        $perPage = $request->get('per_page', 15);
        $posts = $this->postRepository->all($perPage, ['author', 'categories', 'tags', 'media', 'postType'], $filters);
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Display a list of all posts (requires permission)
     */
    public function all(Request $request): JsonResponse
    {
        $filters = $request->only(['s', 'status', 'is_published', 'date_from', 'date_to', 'post_type_id', 'post_type_name', 'seen', 'category_id', 'category_name', 'category_slug', 'lang']);
        $perPage = $request->get('per_page', 15);
        $posts = $this->postRepository->all($perPage, ['author', 'categories', 'tags', 'media', 'postType'], $filters);
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    /**
     * Create a new post
     */
    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $result = $this->postService->createPost($data);
            
            // Check if validation errors were returned
            if (is_array($result) && isset($result['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی داده‌ها',
                    'errors' => $result['errors']
                ], 422);
            }
            
            return response()->json([
                'success' => true,
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            Log::error('Post store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد پست'
            ], 500);
        }
    }

    /**
     * Show a post belonging to the current user
     */
    public function show(string $id): JsonResponse
    {
        try {
            $post = $this->postRepository->find($id)->load(['author', 'categories', 'tags', 'media', 'postType']);
            if ($post->author_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'شما اجازه دسترسی به این پست را ندارید'
                ], 403);
            }
            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت پست'
            ], 500);
        }
    }

    /**
     * Show any post (requires permission)
     */
    public function showAny(string $id): JsonResponse
    {
        try {
            $post = $this->postRepository->find($id)->load(['author', 'categories', 'tags', 'media', 'postType']);
            return response()->json([
                'success' => true,
                'data' => $post
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post showAny failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت پست'
            ], 500);
        }
    }

    /**
     * Update a post belonging to the current user
     */
    public function update(UpdateRequest $request, string $id): JsonResponse
    {
        try {
            $post = $this->postRepository->find($id);

            $result = $this->postService->updatePost($id, $request->validated());
            
            // Check if validation errors were returned
            if (is_array($result) && isset($result['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی داده‌ها',
                    'errors' => $result['errors']
                ], 422);
            }
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post update failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش پست'
            ], 500);
        }
    }

    /**
     * Delete a post belonging to the current user
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $post = $this->postRepository->find($id);

            $this->postService->deletePost($id);
            return response()->json([
                'success' => true,
                'message' => 'پست با موفقیت حذف شد'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف پست'
            ], 500);
        }
    }

    /**
     * Publish the post
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $post = $this->postService->publishPost($id);
            return response()->json([
                'success' => true,
                'message' => 'وضعیت انتشار با موفقط بروز رسانی شد',
                'data' => $post
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post publish failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در انتشار پست'
            ], 500);
        }
    }

    /**
     * Update post status
     */
    public function updateStatus(\Illuminate\Http\Request $request, string $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected,NeedsCorrection',
        ], [
            'status.in' => 'وضعیت انتخاب شده معتبر نیست.',
        ]);
        try {
            $post = $this->postService->updateStatus($id, $request->status);
            return response()->json([
                'success' => true,
                'message' => 'وضعیت پست با موفقیت بروزرسانی شد',
                'data' => $post
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'پست یافت نشد'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Post update status failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی وضعیت پست'
            ], 500);
        }
    }

    /**
     * Increment the seen count for a post (panel)
     */
    public function markSeen($id): \Illuminate\Http\JsonResponse
    {
        $post = $this->postRepository->incrementSeen($id);
        return response()->json([
            'success' => true,
            'seen' => $post->seen
        ]);
    }

    /**
     * List all post types
     */
    public function getPostTypes(): JsonResponse
    {
        $types = \App\Models\PostType::all();
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }
}
