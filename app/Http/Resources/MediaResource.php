<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * Transforms a Media model into a JSON response.
 * We create this so both Posts and Products can reuse it.
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'file_path' => '/' . $this->file_path, // Assumes you have a 'url' helper
            'file_type' => $this->file_type,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
        ];
    }
}
