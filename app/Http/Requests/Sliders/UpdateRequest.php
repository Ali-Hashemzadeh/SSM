<?php

namespace App\Http\Requests\Sliders;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order' => 'nullable|integer',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'exists:media,id',
            'meta' => 'nullable',
            'lang' => 'required'
        ];
    }
} 