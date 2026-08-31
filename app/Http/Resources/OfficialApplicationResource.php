<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficialApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $applicant = $this->whenLoaded('applicant');

        return [
            'id' => $this->application_id,
            'application_id' => $this->application_id,
            'matric_number' => $this->matric_number,
            'applicant_name' => $applicant ? trim($applicant->surname . ' ' . $applicant->firstname) : null,
            'transcript_type' => $this->transcript_type,
            'delivery_mode' => $this->delivery_mode,
            'destination' => $this->destination,
            'recipient' => $this->recipient,
            'app_status' => $this->app_status,
            'address' => $this->address,
            'email' => $this->email,
            'reference' => $this->reference,
            'institutional_username' => $this->institutional_username,
            'cgpa' => $this->cgpa,
            'class_of_degree' => $this->class_of_degree,
            'prog_name' => $this->prog_name,
            'dept' => $this->dept,
            'fac' => $this->fac,
            'qualification' => $this->qualification,
            'graduation_year' => $this->graduation_year,
            'grad_status' => $this->grad_status,
            'certificate' => $this->certificate,
            'form_fields' => $this->form_fields,
            'recommended_by' => $this->recommended_by,
            'recommended_at' => $this->recommended_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->format('m/d/Y'),
            'created_at' => $this->created_at?->format('m/d/Y'),
            'courier_company' => $this->courier_company,
            'courier_contact' => $this->courier_contact,
            'courier_tracking' => $this->courier_tracking,
            'courier_receipt_path' => $this->courier_receipt_path,
            'courier_status' => $this->courier_status,
            'courier_notes' => $this->courier_notes,
            'courier_submitted_at' => $this->courier_submitted_at,
            'applicant' => new ApplicantResource($this->whenLoaded('applicant')),
        ];
    }
}
