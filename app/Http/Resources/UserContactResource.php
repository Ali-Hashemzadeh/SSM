<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A simple resource to show user contact info on an order.
 */
class UserContactResource extends JsonResource
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
            'full_name' => $this->full_name,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'company_name' => $this->company_name,
        ];
    }
}
