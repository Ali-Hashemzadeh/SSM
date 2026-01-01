<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lang' => ['required', 'string', 'in:fa,en,ar'],
            'code' => ['required', 'string', 'size:6'],
            'phone' => [
                'required_if:lang,fa',
                'string',
                'regex:/^09[0-9]{9}$/',
            ],
            'email' => [
                'required_unless:lang,fa',
                'email:rfc,dns',
            ],
        ];
    }
}
