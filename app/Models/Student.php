<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 't_student_test';

    public $timestamps = false;

    protected $fillable = [
        'matric_number', 'SURNAME', 'FIRSTNAME', 'OTHERNAME',
        'EMAIL1', 'prog_code', 'status', 'sex',
    ];

    public function registrations()
    {
        return $this->hasMany(RegistrationResult::class, 'matric_number', 'matric_number');
    }

    public function applicant()
    {
        return $this->hasOne(Applicant::class, 'matric_number', 'matric_number');
    }

    public function programme()
    {
        return $this->belongsTo(CollegeDept::class, 'prog_code', 'prog_code');
    }
}
