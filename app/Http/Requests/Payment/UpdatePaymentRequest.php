<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'paymentReference' => 'required|string',
            'transactionId' => 'required|string',
        ];
    }
}
