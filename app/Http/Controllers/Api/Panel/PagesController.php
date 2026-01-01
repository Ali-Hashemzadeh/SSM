<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pages\StoreRequest;
use App\Http\Requests\Pages\UpdateRequest;
use App\Http\Requests\Pages\UpdateStatusRequest;
use App\Repositories\Pages\PageRepositoryInterface;
use App\Services\PageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class PagesController extends Controller
{
    public function __construct(private PageRepositoryInterface $pageRepository, private PageService $pageService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['s','status','is_published','date_from','date_to']);
        $filters['author_id'] = Auth::id();
        $perPage = $request->get('per_page',15);
        $pages = $this->pageRepository->all($perPage,['author', 'translations'],$filters);
        return response()->json(['success'=>true,'data'=>$pages]);
    }

    public function all(Request $request): JsonResponse
    {
        $filters = $request->only(['s','status','is_published','date_from','date_to','author_id']);
        $perPage = $request->get('per_page',15);
        $pages = $this->pageRepository->all($perPage,['author', 'translations'],$filters);
        return response()->json(['success'=>true,'data'=>$pages]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        try{
            $page = $this->pageService->createPage($request->validated());
            return response()->json(['success'=>true,'data'=>$page],201);
        }catch(\Exception $e){
            Log::error('Page store failed',[ 'method'=>__METHOD__, 'error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'خطا در ایجاد صفحه'],500);
        }
    }

    public function show(string $slug): JsonResponse
    {
        try{
            $page = $this->pageRepository->find($slug)?->load('author', 'translations');
            return response()->json(['success'=>true,'data'=>$page]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'صفحه یافت نشد'],404);
        }
    }

    public function update(UpdateRequest $request,string $id): JsonResponse
    {
        try{
            $page = $this->pageService->updatePage($id,$request->validated());
            return response()->json(['success'=>true,'data'=>$page]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'صفحه یافت نشد'],404);
        }catch(\Exception $e){
            Log::error('Page update failed',[ 'method'=>__METHOD__, 'error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'خطا در بروزرسانی صفحه'],500);
        }
    }

    public function updateStatus(UpdateStatusRequest $request, string $id): JsonResponse
    {
        try{
            $page = $this->pageService->updateStatus($id, $request->validated()['status']);
            return response()->json(['success'=>true,'data'=>$page]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'صفحه یافت نشد'],404);
        }catch(\Exception $e){
            Log::error('Page update failed',[ 'method'=>__METHOD__, 'error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'خطا در بروزرسانی صفحه'],500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try{
            $this->pageService->deletePage($id);
            return response()->json(['success'=>true]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'صفحه یافت نشد'],404);
        }
    }

    public function publish(string $id): JsonResponse
    {
        try{
            $page = $this->pageService->togglePublish($id);
            return response()->json(['success'=>true,'data'=>$page]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'صفحه یافت نشد'],404);
        }
    }
} 