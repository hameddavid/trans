<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class SubmitApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'type' => 'required|string|in:official,student,proficiency',
            'rrr' => 'required|string',
        ];

        if ($this->input('type') === 'official') {
            $rules['recipient_name'] = 'required|string';
            $rules['recipient_address'] = 'required|string';
            $rules['destination_id'] = 'required|string';
            $rules['delivery_mode'] = 'required|string';
            $rules['copies'] = 'sometimes|integer|min:1';
            $rules['certificate'] = 'nullable|file|max:5120';
        }

        return $rules;
    }
}
