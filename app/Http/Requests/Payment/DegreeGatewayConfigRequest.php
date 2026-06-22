<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class DegreeGatewayConfigRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'gateway' => 'nullable|string|in:REMITA,FLUTTERWAVE,remita,flutterwave',
        ];
    }
}
