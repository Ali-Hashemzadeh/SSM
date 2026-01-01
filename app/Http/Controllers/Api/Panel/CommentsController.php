<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comments\StoreRequest;
use App\Http\Requests\Comments\UpdateStatusRequest;
use App\Repositories\Comments\CommentRepositoryInterface;
use App\Services\CommentService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentsController extends Controller
{
    protected CommentRepositoryInterface $commentRepository;
    protected CommentService $commentService;

    public function __construct(CommentRepositoryInterface $commentRepository, CommentService $commentService)
    {
        $this->commentRepository = $commentRepository;
        $this->commentService = $commentService;
    }

    // Display paginated comments list
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $comments = $this->commentRepository->all($perPage, $request->only(['s', 'status', 'date_from', 'date_to', 'author_id']));
        return response()->json([
            'success' => true,
            'data' => $comments,
            'message' => 'لیست نظرات با موفقیت دریافت شد.'
        ]);
    }

    // Display all paginated comments (admin)
    public function all(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $comments = $this->commentRepository->all($perPage, $request->only(['s', 'status', 'date_from', 'date_to', 'author_id']));
        return response()->json([
            'success' => true,
            'data' => $comments,
            'message' => 'لیست نظرات با موفقیت دریافت شد.'
        ]);
    }

    // Add new comment
    public function store(StoreRequest $request)
    {
        try {
            $comment = $this->commentService->createComment($request->validated());
            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'نظر با موفقیت ثبت شد و در انتظار تایید است.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Comment store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت نظر'
            ], 500);
        }
    }

    // Update Comment Status
    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        try {
            $comment = $this->commentService->updateStatus($id, $request->status);
            if (!$comment) {
                return response()->json([
                    'success' => false,
                    'message' => 'نظر مورد نظر یافت نشد.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'data' => $comment,
                'message' => 'وضعیت نظر با موفقیت به‌روزرسانی شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نظر مورد نظر یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Comment update status failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'comment_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی وضعیت نظر'
            ], 500);
        }
    }

    // Delete Comment
    public function destroy($id)
    {
        try {
            $deleted = $this->commentService->deleteComment($id);
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'نظر مورد نظر یافت نشد.'
                ], 404);
            }
            return response()->json([
                'success' => true,
                'message' => 'نظر با موفقیت حذف شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'نظر مورد نظر یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Comment delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'comment_id' => $id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف نظر'
            ], 500);
        }
    }

    /**
     * Display a list of the current user's comments
     */
    public function myComments(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $filters = $request->only(['status', 'date_from', 'date_to']);
        $filters['author_id'] = Auth::id();
        $comments = $this->commentService->getUserComments($perPage, $filters);
        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }
}
