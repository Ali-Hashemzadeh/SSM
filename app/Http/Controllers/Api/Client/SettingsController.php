<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $setting = Setting::find(1);
        return response()->json([
            'success' => true,
            'data' => $setting
        ]);
    }
} 