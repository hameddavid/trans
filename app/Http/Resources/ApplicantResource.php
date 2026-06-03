<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'matric_number' => $this->matric_number,
            'surname' => $this->surname,
            'firstname' => $this->firstname,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'sex' => $this->sex,
            'type' => $this->type,
            'created_at' => $this->created_at?->format('m/d/Y'),
        ];
    }
}
