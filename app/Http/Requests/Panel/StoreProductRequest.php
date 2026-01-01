<?php

namespace App\Http\Requests\Panel;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            // --- REMOVED 'title' and 'description' from here ---

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
            'translations.fa.title' => ['required', 'string', 'max:255'],
            'translations.fa.slug'  => ['required', 'string', 'max:255', 'unique:product_translations,slug'],
            'translations.en.title' => ['required', 'string', 'max:255'],
            'translations.en.slug'  => ['required', 'string', 'max:255', 'unique:product_translations,slug'],

            // Optional translated fields
            'translations.*.description'    => ['nullable', 'string'],
            'translations.*.company_name'   => ['nullable', 'string', 'max:255'],
            'translations.*.material'       => ['nullable', 'string', 'max:255'],
            'translations.*.chrome_plating' => ['nullable', 'string', 'max:255'],
        ];
    }
}
