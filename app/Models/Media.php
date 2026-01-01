<?php

namespace App\Models;

use App\Casts\JalaliDateTime;
use App\Services\WaveformService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_path',
        'file_type',
        'alt_text',
        'caption',
        'uploader_id',
        'type'
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'created_at' => JalaliDateTime::class,
        'updated_at' => JalaliDateTime::class,
    ];

    /**
     * Get the user who uploaded this media.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Get the posts that use this media.
     */
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'mediable');
    }

    public function mediable()
    {
        return $this->morphTo();
    }

    public function sliders()
    {
        return $this->morphedByMany(Slider::class, 'mediable');
    }

    /**
     * Get the waveform data for audio files.
     * This is a virtual attribute that's only included for audio files.
     */
    public function getWaveformDataAttribute()
    {
        // Only generate waveform for audio files

        if (!$this->isAudioFile()) {
            return null;
        }

        // Check if we have cached waveform data
        if (isset($this->attributes['waveform_data'])) {
            return json_decode($this->attributes['waveform_data'], true);
        }
        // Generate waveform data on-the-fly
        $waveformService = app(WaveformService::class);
        $waveformData = $waveformService->generateWaveform(public_path('storage/' . $this->file_path));
        return $waveformData;
    }

    /**
     * Check if this media is an audio file.
     */
    public function isAudioFile(): bool
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

        return in_array($this->file_type, $audioMimeTypes);
    }

    /**
     * Get the model's array form with waveform data for audio files.
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Add waveform data for audio files
        if ($this->isAudioFile()) {
            $array['waveform_data'] = $this->waveform_data;
        }
        
        return $array;
    }
} 