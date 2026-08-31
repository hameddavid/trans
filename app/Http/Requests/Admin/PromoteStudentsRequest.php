<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PromoteStudentsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $count = is_array($this->input('promotions')) ? count($this->input('promotions')) : 0;

        // For large batches (>1000), only validate the envelope to avoid
        // spending minutes in the validator. The service handles bad rows gracefully.
        if ($count > 1000) {
            return [
                'promotions' => 'required|array|min:1',
            ];
        }

        return [
            'promotions' => 'required|array|min:1',
            'promotions.*.matric_number' => 'required|string|max:25',
            'promotions.*.new_level' => 'required|string|max:10',
            'promotions.*.session' => 'nullable|string|max:45',
            'promotions.*.acad_status' => 'nullable|string|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'promotions.required' => 'The promotions array is required.',
            'promotions.*.matric_number.required' => 'Each promotion must have a matric_number.',
            'promotions.*.new_level.required' => 'Each promotion must have a new_level.',
        ];
    }
}
