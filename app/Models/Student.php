<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $table = 't_student_test';

    protected $primaryKey = 'ID';

    public $timestamps = false;

    protected $fillable = [
        'matric_number', 'SURNAME', 'FIRSTNAME',
        'sex', 'BIRTH_DATE', 'ADDRESS', 'LGA',
        'CURRENT_LEVEL', 'STATE_ORIGIN', 'COUNTRY',
        'PROGRAMME', 'CITY_RESIDENT', 'STATE_RESIDENT',
        'MATRIC_DATE', 'GRADUATION_DATE', 'status',
        'LAST_UPDATED_BY', 'LAST_UPDATE_DATE', 'DELETED',
        'EMAIL1', 'EMAIL2', 'STUDENT_PHONE', 'PARENT_PHONE',
        'prog_code', 'PICTURE', 'NOTIFY_SMS',
        'session_admitted', 'session_graduated',
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
