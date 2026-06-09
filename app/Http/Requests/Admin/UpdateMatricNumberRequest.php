<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMatricNumberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'old_matric_number' => 'required|string|max:25',
            'new_matric_number' => 'required|string|max:25|different:old_matric_number',
        ];
    }

    public function messages(): array
    {
        return [
            'new_matric_number.different' => 'New matric number must be different from the old one.',
        ];
    }
}
