<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // These are the rules for the FINAL registration form.
        // Email/phone/code are ALREADY validated by VerifyOtpRequest.
        // We only need the profile data + the temp_token.
        // The service will add the email/phone from the cache.

        return [
            'first_name'      => ['required', 'string', 'max:255'],
            'last_name'       => ['required', 'string', 'max:255'],
            'company_name'    => ['nullable', 'string', 'max:255'],
            'country'         => ['nullable', 'string', 'max:255'],
            'province'        => ['nullable', 'string', 'max:255'],
            'temp_token'      => ['required', 'string'],
            'lang'            => ['required', 'string', Rule::in(['en', 'fa'])],
            'phone'           => ['required_if:lang,fa', 'nullable', 'string', 'max:255'],
            'email'           => ['required_unless:lang,fa', 'nullable', 'string', 'email', 'max:255'],
        ];
    }
}
