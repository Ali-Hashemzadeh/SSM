<?php

namespace App\Http\Requests\Users;

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
            'mobile' => 'required|string|unique:users,mobile|regex:/^09[0-9]{9}$/',
            'password' => [
                'nullable', 'string', 'min:8',
                'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[*&!^%$#@]/', 'confirmed'
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'profile_picture' => [
                'nullable',
                'image',
                function ($attribute, $value, $fail) {
                    if ($value && $value->getClientOriginalExtension() === 'svg') {
                        $fail('SVG files are not allowed.');
                    }
                },
            ],
            'role_id' => 'required|exists:roles,id',
        ];
    }
} 