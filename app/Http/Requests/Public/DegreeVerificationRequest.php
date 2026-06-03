<?php
namespace App\Http\Requests\Public;
use Illuminate\Foundation\Http\FormRequest;

class DegreeVerificationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'surname' => 'required|string',
            'firstname' => 'required|string',
            'programme' => 'required|string',
            'grad_year' => 'required|string',
            'institution_email' => 'required|email',
            'institution_name' => 'required|string',
            'phone' => 'required|string',
        ];
    }
}
