<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isFarsi = $this->input('lang') === 'fa';

        return [
            'lang' => ['required', 'string', 'in:fa,en,ar'], // Add any other langs you support
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
