<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class CheckPendingRRRRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'destination' => 'required|string',
            'gateway' => 'sometimes|string',
        ];
    }
}
