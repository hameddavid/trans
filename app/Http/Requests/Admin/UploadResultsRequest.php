<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadResultsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $count = is_array($this->input('results')) ? count($this->input('results')) : 0;

        // For large batches (>1000), only validate the envelope to avoid
        // spending minutes in the validator. The service handles bad rows gracefully.
        if ($count > 1000) {
            return [
                'session' => 'required|string|regex:/^\d{4}\/\d{4}$/',
                'semester' => 'required|integer|in:1,2',
                'results' => 'required|array|min:1',
            ];
        }

        return [
            'session' => 'required|string|regex:/^\d{4}\/\d{4}$/',
            'semester' => 'required|integer|in:1,2',
            'results' => 'required|array|min:1',
            'results.*.matric_number' => 'required|string|max:25',
            'results.*.course_code' => 'required|string|max:45',
            'results.*.ca' => 'nullable|numeric|min:-1|max:100',
            'results.*.score' => 'nullable|numeric|min:-1|max:100',
            'results.*.total_score' => 'nullable|integer|min:0|max:100',
            'results.*.grade' => 'nullable|string|in:A,B,C,D,E,F',
            'results.*.status' => 'nullable|string|max:1',
            'results.*.unit_id' => 'nullable|string',
            'results.*.lecturer_id' => 'nullable|string|max:30',
            'results.*.flag_waver' => 'nullable|boolean',
            'results.*.course_title' => 'nullable|string',
            'results.*.unit' => 'nullable|integer',
            'results.*.student' => 'nullable|array',
            'results.*.student.surname' => 'required_with:results.*.student|string',
            'results.*.student.firstname' => 'required_with:results.*.student|string',
            'results.*.student.email' => 'nullable|email',
            'results.*.student.prog_code' => 'nullable|string',
            'results.*.student.sex' => 'nullable|string',
            'results.*.student.session_admitted' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'session.regex' => 'Session must be in format YYYY/YYYY (e.g., 2024/2025).',
        ];
    }
}
