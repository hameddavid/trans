<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationActionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'transcript_type' => 'required|string|in:OFFICIAL,STUDENT,PROFICIENCY',
        ];
    }
}
