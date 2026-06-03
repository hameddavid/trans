<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matric_number' => 'required|string',
            'surname' => 'sometimes|string',
            'first_name' => 'sometimes|string',
            'other_name' => 'sometimes|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
            'type' => 'sometimes|string',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['matno'] = $data['matric_number'];
        unset($data['matric_number']);
        return $data;
    }
}
