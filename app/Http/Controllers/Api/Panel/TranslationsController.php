<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use App\Http\Requests\Translations\UpdateRequest;
use App\Services\TranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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

    public function update(UpdateRequest $request, string $locale): JsonResponse
    {
        try {
            $updated = $this->translationService->update($locale, $request->validated()['translations']);
            return response()->json(['success'=>true,'data'=>$updated]);
        } catch (\Exception $e) {
            Log::error('Translation update failed',[ 'error'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
            return response()->json(['success'=>false,'message'=>'خطا در بروزرسانی ترجمه'],500);
        }
    }
} 