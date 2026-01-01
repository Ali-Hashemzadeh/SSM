<?php

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // Validation for the translations array
            'translations' => ['required', 'array'],
            'translations.fa.name' => ['required', 'string', 'max:255'],
            'translations.fa.slug' => ['required', 'string', 'max:255', 'unique:product_category_translations,slug'],
            'translations.en.name' => ['required', 'string', 'max:255'],
            'translations.en.slug' => ['required', 'string', 'max:255', 'unique:product_category_translations,slug'],

            // Add media_id validation
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
