<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $applicant = $this->whenLoaded('applicant');

        return [
            'id' => $this->id,
            'matric_number' => $this->matric_number,
            'applicant_name' => $applicant ? trim($applicant->surname . ' ' . $applicant->firstname) : null,
            'transcript_type' => $this->transcript_type,
            'app_status' => $this->app_status,
            'destination' => $this->destination,
            'recipient' => $this->recipient,
            'cgpa' => $this->cgpa,
            'class_of_degree' => $this->class_of_degree,
            'prog_name' => $this->prog_name,
            'recommended_by' => $this->recommended_by,
            'recommended_at' => $this->recommended_at,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->format('m/d/Y'),
            'created_at' => $this->created_at?->format('m/d/Y'),
            'applicant' => new ApplicantResource($this->whenLoaded('applicant')),
        ];
    }
}
