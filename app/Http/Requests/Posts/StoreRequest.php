<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:posts,slug',
            'content' => 'nullable|string',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'media' => 'nullable|array',
            'media.*' => 'integer|exists:media,id',
            'post_type_id' => 'required|integer|exists:post_types,id',
            'meta' => 'nullable|array',
            'lang' => 'required|string|max:10',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'عنوان پست الزامی است.',
            'slug.required' => 'اسلاگ پست الزامی است.',
            'slug.unique' => 'این اسلاگ قبلا استفاده شده است.',
            'content.required' => 'محتوای پست الزامی است.',
            'status.required' => 'وضعیت پست الزامی است.',
            'categories.*.exists' => 'دسته‌بندی انتخاب شده معتبر نیست.',
            'tags.*.exists' => 'برچسب انتخاب شده معتبر نیست.',
            'media.*.exists' => 'رسانه انتخاب شده معتبر نیست.',
            'post_type_id.required' => 'نوع پست الزامی است.',
            'lang.required' => 'زبان پست الزامی است',
            'meta.required' => 'متای پست الزامی است',
        ];
    }
} 