<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matno' => 'required|string',
            'password' => 'required|string',
        ];
    }
}
