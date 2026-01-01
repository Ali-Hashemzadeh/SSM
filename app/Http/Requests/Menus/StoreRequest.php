<?php

namespace App\Http\Requests\Menus;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'link'  => ['nullable'],
            'parent_id' => ['nullable','exists:menus,id'],
            'order' => ['integer','min:0'],
            'lang' => 'required'
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'   => 'وارد کردن عنوان الزامی است.',
            'title.string'     => 'عنوان باید از نوع رشته باشد.',
            'link.required'    => 'وارد کردن لینک الزامی است.',
            'link.url'         => 'فرمت لینک نامعتبر است.',
            'parent_id.exists' => 'منوی والد انتخاب‌شده معتبر نیست.',
            'order.integer'    => 'فیلد ترتیب باید یک عدد باشد.',
            'order.min'        => 'فیلد ترتیب نمی‌تواند منفی باشد.',
            'lang.required'    => 'فیلد زبان اجباری است.'
        ];
    }
} 