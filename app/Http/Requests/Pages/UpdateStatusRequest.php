<?php

namespace App\Http\Requests\Pages;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PageStatuses;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required','in:'.implode(',', PageStatuses::keys())],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'وضعیت الزامی است.',
            'status.in'       => 'وضعیت انتخاب شده نامعتبر است.',
        ];
    }
} 