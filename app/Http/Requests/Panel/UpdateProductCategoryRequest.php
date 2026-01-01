<?php

namespace App\Http\Requests\Panel;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
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
        $categoryId = $this->route('product_category')->id;

        return [
            'translations' => ['required', 'array'],
            'translations.fa.name' => ['required', 'string', 'max:255'],
            'translations.fa.slug' => ['required', 'string', 'max:255', 'unique:product_category_translations,slug,NULL,id,lang,fa,product_category_id,!' . $categoryId],
            'translations.en.name' => ['required', 'string', 'max:255'],
            'translations.en.slug' => ['required', 'string', 'max:255', 'unique:product_category_translations,slug,NULL,id,lang,en,product_category_id,!' . $categoryId],

            // Add media_id validation
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
        ];
    }
}
