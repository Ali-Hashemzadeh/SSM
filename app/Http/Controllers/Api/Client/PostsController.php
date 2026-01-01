<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\Posts\PostRepositoryInterface;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Comments\StoreRequest;
use App\Models\PostType;
use App\Services\CommentService;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    protected PostRepositoryInterface $postRepository;
    protected CommentService $commentService;

    public function __construct(PostRepositoryInterface $postRepository, CommentService $commentService)
    {
        $this->postRepository = $postRepository;
        $this->commentService = $commentService;
    }

    // get posts
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['s', 'author_id', 'date_from', 'date_to', 'post_type_id', 'post_type_name', 'category_slug', 'lang', 'seen_order', 'order']);
        $filters['is_published'] = true;
        $filters['status'] = 'Approved';
        $posts = $this->postRepository->all($perPage, ['tags', 'categories', 'media', 'author'], $filters);
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }

    public function comment(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $comment = $this->commentService->createComment($data);
        return response()->json([
            'success' => true,
            'data' => $comment
        ], 201);
    }

    // show post
    public function show($id): JsonResponse
    {
        $post = $this->postRepository->find($id);
        $post->load(['tags', 'categories', 'media', 'author']);
        // Only include approved comments
        // $approvedComments = $post->comments()->where('status', 'Approved')->get();
        // $post->setRelation('comments', $approvedComments);
        return response()->json([
            'success' => true,
            'data' => $post
        ]);
    }

    // comments of a post
    public function postComments($id, Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['date_from', 'date_to']);
        $filters['post_id'] = $id;
        $filters['status'] = 'Approved';
        $comments = $this->commentService->getUserComments($perPage, $filters);
        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }

    /**
     * Increment the seen count for a post
     */
    public function markSeen($id): JsonResponse
    {
        $post = $this->postRepository->incrementSeen($id);
        return response()->json([
            'success' => true,
            'seen' => $post->seen
        ]);
    }

    public function getPostTypes(): JsonResponse
    {
        $types = PostType::all();
        return response()->json([
            'success' => true,
            'data' => $types
        ]);
    }
}
