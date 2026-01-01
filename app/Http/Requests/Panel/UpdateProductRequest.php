<?php

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
            // --- REMOVED 'title' and 'description' ---

            // Non-translated fields
            'status' => ['required', Rule::in(['published', 'draft'])],
            'dimensions' => 'nullable|string|max:255',

            // Relationship Validation
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:product_categories,id',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'integer|exists:media,id',

            // --- ADDED VALIDATION FOR TRANSLATIONS ---
            'translations' => ['required', 'array'],

            // Validate 'fa' translation (unique slug, ignoring its own product)
            'translations.fa.title' => ['required', 'string', 'max:255'],
            'translations.fa.slug'  => [
                'required', 'string', 'max:255',
                Rule::unique('product_translations', 'slug')->where(function ($query) use ($productId) {
                    return $query->where('lang', 'fa')->where('product_id', '!=', $productId);
                })
            ],

            // Validate 'en' translation (unique slug, ignoring its own product)
            'translations.en.title' => ['required', 'string', 'max:255'],
            'translations.en.slug'  => [
                'required', 'string', 'max:255',
                Rule::unique('product_translations', 'slug')->where(function ($query) use ($productId) {
                    return $query->where('lang', 'en')->where('product_id', '!=', $productId);
                })
            ],

            // Optional translated fields
            'translations.*.description'    => ['nullable', 'string'],
            'translations.*.company_name'   => ['nullable', 'string', 'max:255'],
            'translations.*.material'       => ['nullable', 'string', 'max:255'],
            'translations.*.chrome_plating' => ['nullable', 'string', 'max:255'],
        ];
    }
}
