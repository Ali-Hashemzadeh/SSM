<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lang = app()->getLocale();
        $translation = $this->translations->firstWhere('lang', $lang);

        return [
            'id' => $this->id,

            // Translated fields
            'title' => $translation ? $translation->title : null,
            'slug' => $translation ? $translation->slug : null,
            'description' => $translation ? $translation->description : null,
            'company_name' => $translation ? $translation->company_name : null,
            'material' => $translation ? $translation->material : null,
            'chrome_plating' => $translation ? $translation->chrome_plating : null,

            // Non-translated fields
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),

            // Relationships
            'categories' => ProductCategoryResource::collection($this->whenLoaded('productCategories')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}
