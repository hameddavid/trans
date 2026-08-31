<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\TranscriptType;
use App\Enums\DeliveryMode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialApplication extends Model
{
    use HasFactory;
    protected $table = 'official_applications';
    protected $primaryKey = 'application_id';

    protected $fillable = [
        'matric_number', 'applicant_id', 'delivery_mode', 'transcript_type',
        'address', 'email', 'destination', 'institutional_username',
        'institutional_password', 'recipient', 'app_status', 'used_token',
        'graduation_year', 'grad_status', 'reference', 'certificate',
        'first_session_in_sch', 'last_session_in_sch', 'years_spent',
        'qualification', 'prog_name', 'dept', 'fac', 'cgpa',
        'class_of_degree', 'transcript_raw', 'recommended_by',
        'recommended_at', 'approved_by', 'approved_at', 'form_fields',
        'edit_token', 'complaint_sent_by', 'complaint_sent_at',
        'courier_company', 'courier_contact', 'courier_tracking',
        'courier_receipt_path', 'courier_status', 'courier_notes',
        'courier_submitted_at',
    ];

    protected $casts = [
        'app_status' => ApplicationStatus::class,
        'transcript_type' => TranscriptType::class,
        'form_fields' => 'array',
    ];

    public function applicant()
    {
        return $this->belongsTo(Applicant::class, 'applicant_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'rrr', 'used_token');
    }
}
