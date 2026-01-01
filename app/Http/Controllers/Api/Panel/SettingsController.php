<?php

namespace App\Http\Controllers\Api\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Repositories\Settings\SettingRepositoryInterface;
use App\Services\SettingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Services\FileUploadService;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    protected SettingRepositoryInterface $settingRepository;
    protected SettingService $settingService;
    protected FileUploadService $fileUploadService;

    public function __construct(SettingRepositoryInterface $settingRepository, SettingService $settingService, FileUploadService $fileUploadService)
    {
        $this->settingRepository = $settingRepository;
        $this->settingService = $settingService;
        $this->fileUploadService = $fileUploadService;
    }
    
    /**
     * Alternative method to create language file when file_put_contents fails
     * 
     * @param string $filePath
     * @param string $content
     * @return bool
     */
    protected function createLanguageFileAlternative(string $filePath, string $content): bool
    {
        try {
            // Try using File facade
            $result = File::put($filePath, $content);
            
            if ($result) {
                return true;
            }
            
            // Try using fopen/fwrite
            $handle = fopen($filePath, 'w');
            if ($handle) {
                $writeResult = fwrite($handle, $content);
                fclose($handle);
                
                if ($writeResult !== false) {
                    return true;
                }
            }
            

            return false;
        } catch (\Exception $e) {
            Log::error('Exception in alternative file creation method', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
            return false;
        }
    }
    
    /**
     * Generate an empty language file based on existing language files
     * 
     * @return string
     */
    protected function generateEmptyLanguageFile(): string
    {
        try {
            // Get keys from an existing language file (e.g., English)
            $existingLangPath = resource_path('lang' . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . 'app.php');
            // Normalize directory path for Windows
            $existingLangPath = str_replace('/', DIRECTORY_SEPARATOR, $existingLangPath);
            
            if (!File::exists($existingLangPath)) {
                $existingLangArray = [];
            } else {
                $existingLangArray = include $existingLangPath;
                if (!is_array($existingLangArray)) {
                    $existingLangArray = [];
                }
            }
            
            // Create a new array with the same keys but empty values
            $emptyLangArray = [];
            foreach ($existingLangArray as $key => $value) {
                $emptyLangArray[$key] = '';
            }
            
            // Generate PHP file content exactly like the original structure
            $content = "<?php\n\nreturn [\n";
            foreach ($emptyLangArray as $key => $value) {
                // Properly escape single quotes in keys
                $escapedKey = str_replace("'", "\\'", $key);
                $content .= "    '{$escapedKey}' => '',\n";
            }
            $content .= "];\n";
            
            Log::info('Generated language file content', [
                'method' => __METHOD__,
                'content_length' => strlen($content),
                'keys_count' => count($emptyLangArray)
            ]);
            
            return $content;
        } catch (\Exception $e) {
            Log::error('Error generating empty language file', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a basic empty language file as fallback
            return "<?php\n\nreturn [];\n";
        }
    }

    // Get the exisitng settings
    public function show(): JsonResponse
    {
        try {
            $setting = $this->settingService->getSetting(1);
            
            // Parse the lang field as JSON if it exists
            if (isset($setting->lang)) {
                $setting->lang = json_decode($setting->lang, true) ?: [];
            }
            
            return response()->json([
                'success' => true,
                'data' => $setting
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'تنظیمات یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Settings show failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تنظیمات.'
            ], 500);
        }
    }
    
    /**
     * Get the list of available languages
     * 
     * @return JsonResponse
     */
    public function getLanguages(): JsonResponse
    {
        try {
            $setting = $this->settingService->getSetting(1);
            $languages = json_decode($setting->lang ?? '[]', true) ?: [];
            
            return response()->json([
                'success' => true,
                'data' => $languages
            ]);
        } catch (\Exception $e) {
            Log::error('Get languages failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست زبان ها.'
            ], 500);
        }
    }

    // Update the settings
    public function update(Request $request): JsonResponse
    {
        $data = $request->only(['title', 'description', 'configs', 'logo']);
        // if ($request->hasFile('logo')) {
        //     $data['logo'] = $request->file('logo');
        // }
        $request->validate([
            'title' => 'nullable|array',
            'description' => 'nullable|array'
        ]);
        // Handle language setting
        if ($request->has('lang')) {
            $lang = $request->input('lang');
            
            // Get current settings to check existing languages
            try {
                $currentSetting = $this->settingService->getSetting(1);
                $currentLangs = json_decode($currentSetting->lang ?? '[]', true) ?: [];
                
                // Check if language already exists in the array
                if (in_array($lang, $currentLangs)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'زبان مورد نظر قبلا اضافه شده است.'
                    ], 400);
                }
                
                // Add the new language to the array
                $currentLangs[] = $lang;
                $data['lang'] = json_encode($currentLangs);
                
                Log::info('Updating language settings', [
                    'method' => __METHOD__,
                    'previous_langs' => $currentSetting->lang,
                    'new_langs' => $data['lang']
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not retrieve current settings, creating new language array', [
                    'method' => __METHOD__,
                    'error' => $e->getMessage()
                ]);
                
                // If we can't get current settings, create a new array with just this language
                $data['lang'] = json_encode([$lang]);
            }
            
            // Check if language directory exists
            $langPath = resource_path('lang/' . $lang);
            // Normalize directory path for Windows
            $langPath = str_replace('/', DIRECTORY_SEPARATOR, $langPath);
            if (!File::exists($langPath)) {
                try {
                    // Create language directory
                    File::makeDirectory($langPath, 0755, true);
                    
                    // Create app.php file with empty values
                    $appPhpContent = $this->generateEmptyLanguageFile();
                    $appFilePath = $langPath . DIRECTORY_SEPARATOR . 'app.php';
                    
                    // Ensure the directory exists again before writing
                    if (!is_dir($langPath)) {
                        mkdir($langPath, 0755, true);
                    }
                    
                    // Use direct file_put_contents with FILE_BINARY flag to ensure file is created
                    $result = file_put_contents($appFilePath, $appPhpContent, LOCK_EX);
                    
                    if ($result === false) {
                        $errorMsg = error_get_last()['message'] ?? 'Unknown error';
                        
                        // Try alternative method
                        $this->createLanguageFileAlternative($appFilePath, $appPhpContent);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception while creating language files', [
                        'method' => __METHOD__,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'lang_path' => $langPath
                    ]);
                }
            }
        }
        
        try {
            $setting = $this->settingService->updateSetting(1, $data);
            return response()->json([
                'success' => true,
                'data' => $setting,
                'message' => 'تنظیمات با موفقیت بروزرسانی شد.'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'تنظیمات یافت نشد.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Settings update failed', [
                'method' => __METHOD__,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $data
            ]);
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی تنظیمات.'
            ], 500);
        }
    }
}
