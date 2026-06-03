<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Applicant extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'surname', 'firstname', 'email', 'password', 'mobile',
        'matric_number', 'sex', 'type',
    ];

    protected $hidden = ['password'];

    public function officialApplications()
    {
        return $this->hasMany(OfficialApplication::class, 'applicant_id');
    }

    public function studentApplications()
    {
        return $this->hasMany(StudentApplication::class, 'applicant_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'matric_number', 'matric_number');
    }
}
