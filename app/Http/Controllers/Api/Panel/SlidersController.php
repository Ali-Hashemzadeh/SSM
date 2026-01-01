<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use App\Models\Media;
use App\Http\Requests\Sliders\StoreRequest;
use App\Http\Requests\Sliders\UpdateRequest;
use App\Services\SliderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class SlidersController extends Controller
{
    protected $sliderService;

    public function __construct(SliderService $sliderService)
    {
        $this->sliderService = $sliderService;
    }

    public function index(): JsonResponse
    {
        try {
            $sliders = $this->sliderService->all();
            return response()->json(['success' => true, 'data' => $sliders]);
        } catch (\Exception $e) {
            Log::error('Slider index failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'خطا در دریافت اسلایدرها'], 500);
        }
    }

    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $slider = $this->sliderService->create($request->validated());
            if ($request->has('media')) {
                $slider->media()->sync($request->input('media'));
            }
            return response()->json(['success' => true, 'data' => $slider->load('media')], 201);
        } catch (\Exception $e) {
            Log::error('Slider store failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'خطا در ایجاد اسلایدر'], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $slider = $this->sliderService->find($id);
            return response()->json(['success' => true, 'data' => $slider]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'اسلایدر یافت نشد'], 404);
        } catch (\Exception $e) {
            Log::error('Slider show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slider_id' => $id
            ]);
            return response()->json(['success' => false, 'message' => 'خطا در دریافت اسلایدر'], 500);
        }
    }

    public function update(UpdateRequest $request, $id): JsonResponse
    {
        try {
            $slider = $this->sliderService->update($id, $request->validated());
            if ($request->has('media_ids')) {
                $slider->media()->sync($request->input('media_ids'));
            }
            return response()->json(['success' => true, 'data' => $slider->load('media')]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'اسلایدر یافت نشد'], 404);
        } catch (\Exception $e) {
            Log::error('Slider update failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slider_id' => $id,
                'request_data' => $request->all()
            ]);
            return response()->json(['success' => false, 'message' => 'خطا در ویرایش اسلایدر'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->sliderService->delete($id);
            return response()->json(['success' => true, 'message' => 'اسلایدر با موفقیت حذف شد']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'اسلایدر یافت نشد'], 404);
        } catch (\Exception $e) {
            Log::error('Slider delete failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'slider_id' => $id
            ]);
            return response()->json(['success' => false, 'message' => 'خطا در حذف اسلایدر'], 500);
        }
    }
} 