<?php

namespace App\Http\Requests\Applicant;

use Illuminate\Foundation\Http\FormRequest;

class InitiatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'destination' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'sometimes|string|in:OFFICIAL,STUDENT,PROFICIENCY,official,student,proficiency',
            'gateway' => 'sometimes|string|in:INTERSWITCH,REMITA,interswitch,remita',
        ];
    }
}
