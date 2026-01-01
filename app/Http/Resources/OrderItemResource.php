<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'quantity' => $this->quantity,

            // --- The Snapshot Data ---
            'title' => $this->title,
            'company_name' => $this->company_name,
            'dimensions' => $this->dimensions,
            'material' => $this->material,
            'chrome_plating' => $this->chrome_plating,
            // --- End Snapshot ---

            // We also include the *current* product data, if it still exists
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
