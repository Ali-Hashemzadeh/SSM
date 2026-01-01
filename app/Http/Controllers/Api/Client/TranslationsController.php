<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;

class TranslationsController extends Controller
{
    public function __construct(private TranslationService $translationService)
    {
    }

    public function show(string $locale): JsonResponse
    {
        $data = $this->translationService->get($locale);
        return response()->json(['success'=>true,'data'=>$data]);
    }
    
    public function languages(): JsonResponse
    {
        $langPath = resource_path('lang');
        $languages = File::directories($langPath);
        
        // Extract only the directory names without the full path
        $languages = array_map(function($path) {
            return basename($path);
        }, $languages);
        
        return response()->json([
            'success' => true,
            'data' => $languages
        ]);
    }
}