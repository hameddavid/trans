<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ForgotMatnoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'surname' => $this->surname,
            'firstname' => $this->firstname,
            'othername' => $this->othername,
            'email' => $this->email,
            'phone' => $this->phone,
            'program' => $this->program,
            'date_left' => $this->date_left,
            'matno_found' => $this->matno_found,
            'status' => $this->status,
            'treated_by' => $this->treated_by,
            'treated_at' => $this->treated_at,
            'created_at' => $this->created_at?->format('m/d/Y'),
        ];
    }
}
