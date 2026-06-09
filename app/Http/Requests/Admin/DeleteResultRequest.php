<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DeleteResultRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'session' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'semester' => 'required|integer|in:1,2',
            'matric_number' => 'required|string|max:25',
            'course_code' => 'required|string|max:45',
        ];
    }
}
