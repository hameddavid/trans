<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DegreeVerificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'surname' => $this->surname,
            'firstname' => $this->firstname,
            'othername' => $this->othername,
            'graduate_name' => trim($this->surname . ' ' . $this->firstname),
            'organization' => $this->institution_name,
            'matric_number' => $this->matno_found,
            'program' => $this->program,
            'grad_year' => $this->grad_year,
            'institution_email' => $this->institution_email,
            'institution_name' => $this->institution_name,
            'phone' => $this->phone,
            'address' => $this->address,
            'request_type' => $this->request_type,
            'matno_found' => $this->matno_found,
            'status' => $this->status,
            'qualification' => $this->qualification,
            'dept' => $this->dept,
            'fac' => $this->fac,
            'treated_by' => $this->treated_by,
            'treated_at' => $this->treated_at,
            'recommended_by' => $this->recommended_by,
            'recommended_at' => $this->recommended_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at,
            'created_at' => $this->created_at?->format('m/d/Y'),
        ];
    }
}
