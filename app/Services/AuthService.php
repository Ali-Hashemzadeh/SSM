<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\SmsHelper;
use App\Mail\SendOtpEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\AuthUserService;
use App\Services\OtpService;
use Illuminate\Support\Facades\Cache; // <-- REMOVED Signer, ADDED Cache
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    protected $userService;
    protected $otpService;

    public function __construct(AuthUserService $userService, OtpService $otpService)
    {
        $this->userService = $userService;
        $this->otpService = $otpService;
    }

    /**
     * Send an OTP to a user (phone or email) and report if the user exists.
     * --- NO CHANGE TO THIS FUNCTION ---
     */
    public function sendOtp(array $data): array
    {
        $isFarsi = $data['lang'] === 'fa';
        $type = $isFarsi ? 'mobile' : 'email';
        $recipient = $isFarsi ? $data['phone'] : $data['email'];

        $userExists = (bool) $this->userService->findUserByRecipient($recipient, $type);
        $code = $this->otpService->generateOtp($recipient, $type);

        try {
            if ($isFarsi) {
                SmsHelper::sendOtpSms($recipient, $code);
            } else {
                Mail::to($recipient)->send(new SendOtpEmail($code));
                Log::info("Email OTP sent for {$recipient}: {$code}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to {$recipient}", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to send OTP.', 'status' => 500];
        }

        return [
            'success' => true,
            'message' => 'OTP sent successfully.',
            'data' => [
                'user_exists' => $userExists,
                'recipient' => $recipient,
                'type' => $type
            ]
        ];
    }

    /**
     * NEW: Verify an OTP for login OR registration.
     */
    public function verifyOtp(array $data): array
    {
        $isFarsi = $data['lang'] === 'fa';
        $type = $isFarsi ? 'mobile' : 'email';
        $recipient = $isFarsi ? $data['phone'] : $data['email'];

        // 1. Check if the OTP is valid
        $otp = $this->otpService->getValidOtp($recipient, $data['code'], $type);
        if (!$otp) {
            return ['success' => false, 'message' => 'Invalid or expired OTP.', 'status' => 401];
        }

        // 2. OTP is valid, so delete it.
        $this->otpService->deleteOtp($otp);

        // 3. Check if user exists
        $user = $this->userService->findUserByRecipient($recipient, $type);

        if ($user) {
            // --- CASE 1: USER EXISTS (LOGIN) ---
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;
            return $this->formatSuccessResponse($user, $token, 'Login successful.');

        } else {
            // --- CASE 2: NEW USER (REGISTRATION) ---
            // --- REFACTORED: Use Cache instead of Signer ---

            $payload = [
                'type' => $type,
                'recipient' => $recipient
            ];

            // 1. Generate a simple, random token
            $tempToken = Str::random(60);

            // 2. Store the payload in the cache for 15 minutes, using the token as the key
            Cache::put('reg_token_' . $tempToken, $payload, now()->addMinutes(15));

            return [
                'success' => true,
                'message' => 'OTP verified. Please complete your registration.',
                'status' => 200,
                'data' => [
                    'user_exists' => false,
                    'temp_token' => $tempToken // Send the simple token to the user
                ]
            ];
        }
    }


    /**
     * NEW: Register a new user with a valid temp_token.
     */
    public function completeRegistration(array $data): array
    {
        // 1. Validate the temporary token by checking the Cache
        // --- REFACTORED: Use Cache instead of Signer ---

        $payload = Cache::get('reg_token_' . $data['temp_token']);

        if (!$payload) {
            // If the key doesn't exist, the token is invalid or expired
            return ['success' => false, 'message' => 'Invalid or expired registration token.', 'status' => 401];
        }

        // 2. Token is valid. Remove it from the cache so it can't be used again.
        Cache::forget('reg_token_' . $data['temp_token']);

        // --- End of refactor ---

        // 3. Extract recipient data from the valid payload
        $type = $payload['type'];
        $recipient = $payload['recipient'];

        // 4. Double-check user doesn't exist
        if ($this->userService->findUserByRecipient($recipient, $type)) {
            return ['success' => false, 'message' => 'User already exists.', 'status' => 409];
        }

        // 5. Merge token data with form data to create the user
        $userData = $data;
        if ($type === 'mobile') {
            $userData['mobile'] = $recipient;
        } else {
            $userData['email'] = $recipient;
        }

        // Add a placeholder password
        $userData['password'] = Hash::make(Str::random(20));

        // 6. Create the user
        $user = $this->userService->registerNewUser($userData);

        // 7. Log them in
        $token = $user->createToken('auth_token')->plainTextToken;
        return $this->formatSuccessResponse($user, $token, 'Registration successful.');
    }

    /**
     * Formats the standard successful auth response.
     * --- NO CHANGE TO THIS FUNCTION ---
     */
    private function formatSuccessResponse(User $user, string $token, string $message): array
    {
        $user->load('role'); // Eager load the role for the response
        return [
            'success' => true,
            'message' => $message,
            'status' => 200,
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
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ];
    }
}
