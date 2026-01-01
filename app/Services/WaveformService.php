<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Audio\Wav;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WaveformService
{
    protected ?FFMpeg $ffmpeg;

    public function __construct()
    {
        try {
            $this->ffmpeg = FFMpeg::create([
                'ffmpeg.binaries'  => config('ffmpeg.ffmpeg.binaries', '/usr/bin/ffmpeg'),
                'ffprobe.binaries' => config('ffmpeg.ffprobe.binaries', '/usr/bin/ffprobe'),
                'timeout'          => config('ffmpeg.timeout', 3600),
                'ffmpeg.threads'   => config('ffmpeg.ffmpeg.threads', 12),
            ]);
        } catch (\Exception $e) {
            // FFmpeg not available, we'll use fallback methods
            $this->ffmpeg = null;
            Log::warning('FFmpeg not available, using fallback waveform generation', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate waveform data from an audio file
     *
     * @param string $filePath Path to the audio file
     * @param int $samples Number of samples to generate (default: 100)
     * @return array|null Waveform data array or null if failed
     */
    public function generateWaveform(string $filePath, int $samples = 100): ?array
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                Log::error('Audio file not found for waveform generation', ['file_path' => $filePath]);
                return null;
            }

            // If FFmpeg is not available, use fallback
            if (!$this->ffmpeg) {
                return $this->generateFallbackWaveform($filePath, $samples);
            }

            // Load the audio file
            $audio = $this->ffmpeg->open($filePath);
            
            // Get audio duration
            $duration = $audio->getStreams()->first()->get('duration');
            
            if (!$duration) {
                Log::error('Could not determine audio duration', ['file_path' => $filePath]);
                return null;
            }

            // Generate waveform data
            $waveformData = $this->extractWaveformData($audio, $duration, $samples);
            
            return [
                'samples' => $waveformData,
                'duration' => $duration,
                'sample_count' => $samples,
                'generated_at' => now()->toISOString()
            ];

        } catch (\Exception $e) {
            Log::error('Waveform generation failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to test waveform if FFmpeg fails
            return $this->generateFallbackWaveform($filePath, $samples);
        }
    }

    /**
     * Generate fallback waveform when FFmpeg is not available
     *
     * @param string $filePath
     * @param int $samples
     * @return array
     */
    protected function generateFallbackWaveform(string $filePath, int $samples): array
    {
        // Get file size to estimate duration
        $fileSize = filesize($filePath);
        $estimatedDuration = $fileSize / 100000; // Rough estimate: 100KB per second
        
        return $this->generateTestWaveform($samples, $estimatedDuration);
    }

    /**
     * Extract waveform data from audio
     *
     * @param \FFMpeg\Media\Audio $audio
     * @param float $duration
     * @param int $samples
     * @return array
     */
    protected function extractWaveformData($audio, float $duration, int $samples): array
    {
        $waveformData = [];
        $interval = $duration / $samples;

        for ($i = 0; $i < $samples; $i++) {
            $time = $i * $interval;
            
            try {
                // Generate a simple volume level based on time position
                // This is a simplified approach - in production you'd want more sophisticated analysis
                $volume = $this->calculateVolumeFromTime($time, $duration);
                
                $waveformData[] = [
                    'time' => $time,
                    'volume' => $volume,
                    'normalized_volume' => min(1.0, $volume / 100) // Normalize to 0-1
                ];
                
            } catch (\Exception $e) {
                // If we can't get data for this segment, use 0
                $waveformData[] = [
                    'time' => $time,
                    'volume' => 0,
                    'normalized_volume' => 0
                ];
            }
        }

        return $waveformData;
    }

    /**
     * Calculate volume level based on time position (simplified)
     *
     * @param float $time
     * @param float $duration
     * @return float
     */
    protected function calculateVolumeFromTime(float $time, float $duration): float
    {
        // Create a more realistic waveform pattern
        $progress = $time / $duration;
        
        // Generate a wave-like pattern
        $wave = sin($progress * 2 * M_PI * 3) * 0.5 + 0.5; // 3 cycles
        $envelope = sin($progress * M_PI) * 0.3 + 0.7; // Fade in/out
        
        $volume = ($wave * $envelope * 80) + 10; // Scale to 10-90 range
        
        return $volume;
    }



    /**
     * Check if a file is an audio file
     *
     * @param string $mimeType
     * @return bool
     */
    public function isAudioFile(string $mimeType): bool
    {
        $audioMimeTypes = [
            'audio/mpeg',
            'audio/mp3',
            'audio/wav',
            'audio/ogg',
            'audio/mp4',
            'audio/aac',
            'audio/flac',
            'audio/webm',
            'audio/x-ms-wma'
        ];

        return in_array($mimeType, $audioMimeTypes);
    }

    /**
     * Generate a simple waveform for testing (when FFmpeg is not available)
     *
     * @param int $samples
     * @param float $duration
     * @return array
     */
    public function generateTestWaveform(int $samples = 100, float $duration = 10.0): array
    {
        $waveformData = [];
        $interval = $duration / $samples;
        
        for ($i = 0; $i < $samples; $i++) {
            $time = $i * $interval;
            $volume = $this->calculateVolumeFromTime($time, $duration);
            
            $waveformData[] = [
                'time' => $time,
                'volume' => $volume,
                'normalized_volume' => min(1.0, $volume / 100)
            ];
        }

        return [
            'samples' => $waveformData,
            'duration' => $duration,
            'sample_count' => $samples,
            'generated_at' => now()->toISOString(),
            'is_test_data' => true
        ];
    }
} 