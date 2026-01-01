<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');
        return [
            'mobile' => 'sometimes|string|unique:users,mobile,' . $userId . '|regex:/^09[0-9]{9}$/',
            'password' => [
                'sometimes', 'string', 'min:8',
                'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[*&!^%$#@]/', 'confirmed'
            ],
            'P' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'profile_picture' => [
                'nullable',
                'image',
                function ($attribute, $value, $fail) {
                    if ($value && $value->getClientOriginalExtension() === 'svg') {
                        $fail('SVG files are not allowed.');
                    }
                },
            ],
            'role_id' => 'sometimes|exists:roles,id',
        ];
    }
    
    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'mobile.unique' => 'شماره موبایل وارد شده قبلاً ثبت شده است.',
            'mobile.regex' => 'فرمت شماره موبایل معتبر نیست.',
            'password.min' => 'گذرواژه باید حداقل ۸ کاراکتر باشد.',
            'password.regex' => 'گذرواژه باید شامل حروف کوچک، حروف بزرگ، عدد و یکی از کاراکترهای خاص (*&!^%$#@) باشد.',
            'password.confirmed' => 'تکرار گذرواژه با گذرواژه اصلی مطابقت ندارد.',
            'P.max' => 'نام وارد شده نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'last_name.max' => 'نام خانوادگی وارد شده نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'profile_picture.image' => 'فایل انتخابی باید یک تصویر باشد.',
            'profile_picture.svg' => 'فرمت فایل SVG مجاز نیست.',
            'role_id.exists' => 'نقش انتخابی معتبر نیست.',
        ];
    }
}
