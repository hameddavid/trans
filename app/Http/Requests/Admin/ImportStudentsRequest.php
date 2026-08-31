<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportStudentsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $count = is_array($this->input('students')) ? count($this->input('students')) : 0;

        // For large batches (>1000), only validate the envelope to avoid
        // spending minutes in the validator. The service handles bad rows gracefully.
        if ($count > 1000) {
            return [
                'students' => 'required|array|min:1',
            ];
        }

        return [
            'students' => 'required|array|min:1',
            'students.*.matric_number' => 'required|string|max:25',
            'students.*.surname' => 'required|string|max:35',
            'students.*.firstname' => 'required|string|max:35',
            'students.*.email' => 'nullable|email|max:255',
            'students.*.prog_code' => 'nullable|string|max:10',
            'students.*.sex' => 'nullable|string|max:5',
            'students.*.session_admitted' => 'nullable|string|max:45',
            'students.*.status' => 'nullable|string|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'students.required' => 'The students array is required.',
            'students.*.matric_number.required' => 'Each student must have a matric_number.',
            'students.*.surname.required' => 'Each student must have a surname.',
            'students.*.firstname.required' => 'Each student must have a firstname.',
        ];
    }
}
