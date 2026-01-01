<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\MediaResource; // <-- 1. Import this

class ProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = app()->getLocale();
        $translation = $this->translations->firstWhere('lang', $lang);

        return [
            'id' => $this->id,
            'name' => $translation ? $translation->name : null,
            'slug' => $translation ? $translation->slug : null,

            // 2. Add the image object
            // We use the 'image' accessor we created in the model
            // and format it using your existing MediaResource.
            'image' => $this->whenLoaded('media', function () {
                // We use the 'image' accessor, which is now safe to call
                return new MediaResource($this->image);
            }),
        ];
    }
}
