<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'quantity' => $this->quantity,
            // Eager load the product resource if it's available
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
