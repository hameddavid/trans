<?php

namespace App\Models;

use App\Enums\TranscriptType;
use Illuminate\Database\Eloquent\Model;

class AdminApplication extends Model
{
    protected $table = 'admin_applications';

    protected $fillable = [
        'matric_number', 'admin_id', 'delivery_mode', 'transcript_type',
        'address', 'destination', 'recipient', 'app_status',
        'graduation_year', 'grad_status', 'certificate',
        'first_session_in_sch', 'last_session_in_sch', 'years_spent',
        'qualification', 'prog_name', 'dept', 'fac', 'cgpa',
        'class_of_degree', 'transcript_raw',
    ];

    protected $casts = [
        'transcript_type' => TranscriptType::class,
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'matric_number', 'matric_number');
    }
}
