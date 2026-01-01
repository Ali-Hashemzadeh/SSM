<?php

namespace App\Http\Requests\Translations;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'translations' => ['required','array'],
            'translations.*' => ['string'],
        ];
    }
} 