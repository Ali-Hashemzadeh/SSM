<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use App\Services\SliderService;
use App\Services\PostService;

class HomeController extends Controller
{
    protected $sliderService;
    protected $postService;

    public function __construct(SliderService $sliderService, PostService $postService)
    {
        $this->sliderService = $sliderService;
        $this->postService = $postService;
    }

    public function index(): JsonResponse
    {
        $slider = $this->postService->getRecentByPostType('slider', 8);
        $services = $this->postService->getRecentByPostType('services', 3);
        $statistics = $this->postService->getRecentByPostType('statistics', 4);
        $recent_products = $this->postService->getRecentByPostType('recent_products', 6);
        $customer_reviews = $this->postService->getRecentByPostType('customer_reviews', 4);
        $articles = $this->postService->getRecentByPostType('articles', 3);
        $settings = Setting::first();

        return response()->json([
            'slider' => $slider,
            'services' => $services,
            'statistics' => $statistics,
            'recent_products' => $recent_products,
            'customer_reviews' => $customer_reviews,
            'articles' => $articles,
            'settings' => $settings
        ]);
    }
}
