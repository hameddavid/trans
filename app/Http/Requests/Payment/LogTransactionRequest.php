<?php
namespace App\Http\Requests\Payment;
use Illuminate\Foundation\Http\FormRequest;

class LogTransactionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'gateway' => 'required|string',
            'destination' => 'required|string',
            'rrr' => 'required|string',
            'orderID' => 'required|string',
            'amount' => 'required|string',
        ];
    }
}
