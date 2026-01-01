<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // Validation rules for storing a comment
    public function rules()
    {
        return [
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ];
    }

} 