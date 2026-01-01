<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:51200'], // 50MB max
            'alt_text' => 'nullable|string|max:255',
            'caption' => 'nullable|string|max:1000',
            'size' => 'nullable',
            'size.original' => 'nullable',
            'size.original.width' => 'required_with:size.original',
            'size.original.height' => 'required_with:size.original',
            'size.thumbnail' => 'nullable',
            'size.thumbnail.width' => 'required_with:size.thumbnail',
            'size.thumbnail.height' => 'required_with:size.thumbnail',
        ];
    }
}