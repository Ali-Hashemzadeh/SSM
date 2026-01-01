<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:categories,name',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'parent_id' => 'nullable|exists:categories,id',
            'post_type_id' => 'nullable|exists:post_types,id',
            'lang' => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'نام دسته‌بندی الزامی است.',
            'slug.required' => 'اسلاگ دسته‌بندی الزامی است.',
            'slug.unique' => 'این اسلاگ قبلا استفاده شده است.',
            'name.unique' => 'این نام قبلا استفاده شده است.',
            'parent_id.exists' => 'دسته‌بندی والد معتبر نیست.',
            'post_type_id.exists' => 'نوع پست معتبر نیست.',
            'lang.required' => 'زبان دسته بندی الزامی است'
        ];
    }
}
