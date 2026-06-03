<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class ForgotMatricRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'surname' => 'required|string',
            'firstname' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'program' => 'required|string',
            'date_left' => 'required|string',
        ];
    }
}
