<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class CheckAvailabilityRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matno' => 'required|string',
        ];
    }
}
