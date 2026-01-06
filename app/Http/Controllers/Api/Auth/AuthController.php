<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // We will remove this
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest; // <-- NEW
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send an OTP for login or registration.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->sendOtp($request->validated());
            return response()->json($response, $response['status'] ?? 200);

        } catch (\Exception $e) {
            Log::error('OTP sending failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * NEW: Verify an OTP.
     * This will log in a user if they exist, or
     * return a temporary token if they don't.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->verifyOtp($request->validated());
            return response()->json($response, $response['status'] ?? 200);

        } catch (\Exception $e) {
            Log::error('OTP Verification failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }


    /**
     * NEW: Register a new user using a valid temp_token and form data.
     * Renamed from registerWithOtp
     */
    public function completeRegistration(RegisterRequest $request): JsonResponse
    {
        try {
            // We now pass the temp_token to the service
            $data = $request->validated();
            $data['temp_token'] = $request->input('temp_token'); // Make sure temp_token is passed

            $response = $this->authService->completeRegistration($data);
            return response()->json($response, $response['status'] ?? 200);

        } catch (\Exception $e) {
            Log::error('Registration completion failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An internal error occurred.'], 500);
        }
    }

    /**
     * REMOVED: loginWithOtp
     * This function is now replaced by verifyOtp
     */
    // public function loginWithOtp(LoginRequest $request): JsonResponse { ... }


    /**
     * REMOVED: registerWithOtp
     * This function is now replaced by completeRegistration
     */
    // public function registerWithOtp(RegisterRequest $request): JsonResponse { ... }


    /**
     * Logout user
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['success' => true, 'message' => 'خروج با موفقیت انجام شد']);
        } catch (\Exception $e) {
            Log::error('User logout failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id ?? null]);
            return response()->json(['success' => false, 'message' => 'خطا در خروج'], 500);
        }
    }

    /**
     * Get authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('role'); // Eager load role
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'mobile' => $user->mobile,
                        'email' => $user->email,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'full_name' => $user->full_name,
                        'company_name' => $user->company_name,
                        'country' => $user->country,
                        'province' => $user->province,
                        'role' => $user->role->only(['slug', 'name']),
                        'created_at' => $user->created_at,
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('User profile retrieval failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id ?? null]);
            return response()->json(['success' => false, 'message' => 'خطا در دریافت اطلاعات کاربر'], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $request->user()->currentAccessToken()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'توکن با موفقیت تمدید شد',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Token refresh failed', ['error' => $e->getMessage(), 'user_id' => $request->user()->id ?? null]);
            return response()->json(['success' => false, 'message' => 'خطا در تمدید توکن'], 500);
        }
    }

    public function loginWithPassword(Request $request): JsonResponse
    {
        // 1. Validate Input
        // We expect EITHER email OR mobile to be present
        $fields = $request->validate([
            'email' => 'required_without:mobile|nullable|email',
            'mobile' => 'required_without:email|nullable|string',
            'password' => 'required|string',
        ]);

        // 2. Find User (Check Email OR Mobile)
        // We initialize the query and apply the condition based on which field exists
        $user = User::query();

        if ($request->filled('email')) {
            $user->where('email', $fields['email']);
        } else {
            $user->where('mobile', $fields['mobile']);
        }

        $user = $user->first();

        // 3. Check Password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response()->json([
                'success' => false,
                // Update message to reflect both possibilities
                'message' => 'اطلاعات ورود (ایمیل/موبایل یا رمز عبور) اشتباه است.'
            ], 401);
        }

        // 4. Generate Token
        // $user->tokens()->delete(); // Optional: Clear old tokens
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Return Response
        return response()->json([
            'success' => true,
            'message' => 'ورود با موفقیت انجام شد',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email, // Added email to response
                    'mobile' => $user->mobile,
                    'full_name' => $user->full_name,
                    // Ensure the 'role' relationship exists in your User model
                    'role' => $user->relationLoaded('role') ? $user->role->only(['slug', 'name']) : null,
                ],
            ]
        ]);
    }
}
