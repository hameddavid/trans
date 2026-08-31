<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAdminApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matric_number' => 'required|string',
            'transcript_type' => 'required|string|in:OFFICIAL,STUDENT',
            'recipient' => 'nullable|string',
        ];
    }
}
