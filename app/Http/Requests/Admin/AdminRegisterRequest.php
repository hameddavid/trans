<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class AdminRegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'surname' => 'required|string',
            'firstname' => 'required|string',
            'othername' => 'sometimes|string',
            'phone' => 'required|string',
            'email' => 'required|email|unique:admin,email',
            'title' => 'sometimes|string',
            'role' => 'required|string|in:200,300',
        ];
    }
}
