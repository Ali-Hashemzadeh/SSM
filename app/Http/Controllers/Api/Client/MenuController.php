<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index(): JsonResponse
    {
        if(!is_null(request('lang'))){
            $menus = Menu::where('parent_id', null)->where('lang', request('lang'))->with('children')->get();
        }else{
            $menus = Menu::where('parent_id', null)->with('children')->get();
        }
        return response()->json([
            'success' => true,
            'data' => $menus
        ]);
    }
} 