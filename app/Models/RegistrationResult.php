<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationResult extends Model
{
    protected $table = 'registrations';

    public $timestamps = false;

    protected $fillable = [
        'matric_number', 'session_id', 'semester', 'course_code',
        'lecturer_id', 'status', 'ca', 'score', 'total_score',
        'grade', 'remarks', 'deleted', 'unit_id', 'flag_waver',
    ];

    protected $casts = [
        'semester' => 'integer',
        'ca' => 'decimal:2',
        'score' => 'decimal:2',
        'total_score' => 'integer',
        'flag_waver' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'matric_number', 'matric_number');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_code', 'course_code');
    }
}
