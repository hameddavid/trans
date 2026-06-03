<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\TranscriptType;
use Illuminate\Database\Eloquent\Model;

class StudentApplication extends Model
{
    protected $table = 'student_applications';

    protected $fillable = [
        'matric_number', 'applicant_id', 'delivery_mode', 'transcript_type',
        'address', 'destination', 'recipient', 'app_status',
        'graduation_year', 'grad_status', 'certificate',
        'first_session_in_sch', 'last_session_in_sch', 'years_spent',
        'qualification', 'prog_name', 'dept', 'fac', 'cgpa',
        'class_of_degree', 'transcript_raw', 'recommended_by',
        'recommended_at', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'app_status' => ApplicationStatus::class,
        'transcript_type' => TranscriptType::class,
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }
}
