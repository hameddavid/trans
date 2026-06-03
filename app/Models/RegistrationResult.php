<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationResult extends Model
{
    protected $table = 'registrations';

    public $timestamps = false;

    protected $fillable = [
        'matric_number', 'session_id', 'semester', 'course_code',
        'unit_id', 'status', 'ca', 'score', 'total_score',
        'grade', 'deleted', 'flag_waver',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'matric_number', 'matric_number');
    }
}
