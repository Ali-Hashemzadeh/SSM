<?php

namespace App\Http\Requests\Posts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    public function rules(): array
    {
        $postId = $this->route('id');
        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255|unique:posts,slug,' . $postId,
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|required',
            'published_at' => 'nullable|date',
            'categories' => 'nullable|array',
            'categories.*' => 'integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
            'media' => 'nullable|array',
            'media.*' => 'integer|exists:media,id',
            'post_type_id' => 'sometimes|integer|exists:post_types,id',
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
            'lang.required' => 'زبان پست الزامی است'

        ];
    }
} 