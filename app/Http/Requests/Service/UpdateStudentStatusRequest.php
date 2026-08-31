<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'students' => 'required|array|min:1',
            'students.*.matric_number' => 'required|string|max:25',
            'students.*.status' => 'required|string|max:15',
            'students.*.session_graduated' => 'nullable|string|max:45',
        ];
    }

    public function messages(): array
    {
        return [
            'students.required' => 'The students array is required.',
            'students.*.matric_number.required' => 'Each student must have a matric_number.',
            'students.*.status.required' => 'Each student must have a status.',
        ];
    }
}
