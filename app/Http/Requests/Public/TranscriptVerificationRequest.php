<?php
namespace App\Http\Requests\Public;
use Illuminate\Foundation\Http\FormRequest;

class TranscriptVerificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'used_token' => 'required|string',
            'matno' => 'required|string',
        ];
    }
}
