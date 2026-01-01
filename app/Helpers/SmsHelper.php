<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsHelper
{
    /**
     * Sends an OTP SMS using the Datees.net API.
     *
     * @param string $recipientMobile
     * @param string $otpCode
     * @return bool
     */
    public static function sendOtpSms(string $recipientMobile, string $otpCode): bool
    {
        try {
            // Construct the full URL with query parameters
            $baseUrl = 'https://api.datees.net/sms/sms.php';
            $query = http_build_query([
                'pattern' => 'w4t78v73puhstvy',
                'projectid' => 147,
                'recipient' => $recipientMobile,
                'vars[code]' => $otpCode,
            ]);
            $url = $baseUrl . '?' . $query;

            // Initialize a cURL session
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            // Execute the cURL request
            $response = curl_exec($ch);
            
            // Get the HTTP status code and any cURL errors
            $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            
            // Close the cURL session
            curl_close($ch);

            // Handle cURL errors
            if ($curlError) {
                Log::error('cURL error during SMS sending', [
                    'error' => $curlError,
                    'mobile' => $recipientMobile,
                ]);
                return false;
            }
            return $httpStatus === 200;

        } catch (\Exception $e) {
            Log::error('OTP SMS sending failed', [
                'error' => $e->getMessage(),
                'mobile' => $recipientMobile,
            ]);
            return false;
        }
    }
}
