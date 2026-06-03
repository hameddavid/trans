<?php
namespace App\Http\Requests\Applicant;
use Illuminate\Foundation\Http\FormRequest;

class EditApplicationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'userid' => 'required',
            'matno' => 'required|string',
            'token' => 'required|string',
            'appid' => 'required',
            'requestType' => 'required|string|in:check_token,update',
        ];
    }
}
