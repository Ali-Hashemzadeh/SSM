<?php

namespace App\Http\Requests\Comments;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CommentStatuses;

class UpdateStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    // Validation rules for updating comment status
    public function rules()
    {
        return [
            'status' => ['required', 'in:' . implode(',', array_column(CommentStatuses::cases(), 'name'))],
        ];
    }
} 