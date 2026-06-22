<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class DegreeLogTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'matno' => 'required|string|max:25',
            'email' => 'required|email',
            'names' => 'required|string',
            'gateway' => 'required|string',
            'destination' => 'required|string',
            'rrr' => 'required|string',
            'orderID' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'institution_email' => 'nullable|email',
            'institution_name' => 'nullable|string',
        ];
    }
}
