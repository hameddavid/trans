<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class SubmitAdminApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matno' => 'required|string',
            'transcript_type' => 'required|string|in:OFFICIAL,STUDENT',
            'recipient' => 'required|string',
        ];
    }
}
