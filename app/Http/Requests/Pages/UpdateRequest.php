<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('page'); // depending on route param
        return [
            'slug'         => ['required', 'string', 'max:255', 'unique:pages,slug,' . $id],
            'media'        => ['nullable', 'array'],
            'media.*'      => ['integer', 'exists:media,id'],
            'status'       => ['nullable', 'in:' . implode(',', \App\Enums\PageStatuses::keys())],
            
            'translations' => ['required', 'array'],
            'translations.*.title'   => ['required', 'max:255'],
            'translations.*.content' => ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required'  => 'وارد کردن اسلاگ الزامی است.',
            'slug.string'    => 'اسلاگ باید متن باشد.',
            'slug.max'       => 'اسلاگ نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'slug.unique'    => 'این اسلاگ قبلاً استفاده شده است.',
            'media.array'        => 'مدیا باید یک آرایه باشد.',
            'media.*.integer'    => 'شناسه‌های مدیا باید عددی باشند.',
            'media.*.exists'     => 'مدیای انتخاب‌شده یافت نشد.',
            
            'translations.required'        => 'ترجمه‌ها الزامی است.',
            'translations.array'           => 'ترجمه‌ها باید یک آرایه باشد.',
            'translations.*.title.required'  => 'وارد کردن عنوان برای هر ترجمه الزامی است.',
            'translations.*.title.max'       => 'عنوان نباید بیشتر از ۲۵۵ کاراکتر باشد.',
        ];
    }
}