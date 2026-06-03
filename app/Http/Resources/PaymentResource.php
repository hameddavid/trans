<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matric_number' => $this->matric_number,
            'names' => $this->names,
            'email' => $this->email,
            'amount' => $this->amount,
            'rrr' => $this->rrr,
            'destination' => $this->destination,
            'type' => $this->destination,
            'gateway' => $this->gateway,
            'status_code' => $this->status_code,
            'status_msg' => $this->status_msg,
            'status' => strtoupper($this->status_msg ?? 'pending'),
            'app_id' => $this->app_id,
            'created_at' => $this->created_at?->format('m/d/Y'),
        ];
    }
}
