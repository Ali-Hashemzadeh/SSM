<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Menus\StoreRequest;
use App\Http\Requests\Menus\UpdateRequest;
use App\Repositories\Menus\MenuRepositoryInterface;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class MenusController extends Controller
{
    public function __construct(private MenuRepositoryInterface $menuRepository, private MenuService $menuService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['s','parent_id']);
        $perPage = $request->get('per_page',15);
        
        $shouldPaginate = filter_var($request->get('paginate', true), FILTER_VALIDATE_BOOLEAN);

        $menus = $this->menuRepository->all($perPage, ['children'], $filters, $shouldPaginate);
        
        return response()->json(['success'=>true,'data'=>$menus]);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $menu = $this->menuService->createMenu($request->validated());
            return response()->json(['success'=>true,'data'=>$menu],201);
        } catch(\Exception $e){
            Log::error('Menu store failed',[ 'error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'خطا در ایجاد منو'],500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try{
            $menu = $this->menuRepository->find($id)->load('children');
            return response()->json(['success'=>true,'data'=>$menu]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'منو یافت نشد'],404);
        }
    }

    public function update(UpdateRequest $request,string $id): JsonResponse
    {
        try{
            $menu = $this->menuService->updateMenu($id,$request->validated());
            return response()->json(['success'=>true,'data'=>$menu]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'منو یافت نشد'],404);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try{
            $this->menuService->deleteMenu($id);
            return response()->json(['success'=>true]);
        }catch(ModelNotFoundException $e){
            return response()->json(['success'=>false,'message'=>'منو یافت نشد'],404);
        }
    }
} 