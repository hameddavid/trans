<?php

namespace App\Models;

use App\Enums\DegreeVerificationStatus;
use Illuminate\Database\Eloquent\Model;

class DegreeVerification extends Model
{
    protected $table = 'degree_verification';

    protected $fillable = [
        'surname', 'firstname', 'othername', 'program', 'grad_year',
        'institution_email', 'institution_name', 'phone', 'address',
        'request_type', 'matno_found', 'status', 'used_token',
        'yr_of_adms', 'qualification', 'dept', 'fac',
        'treated_by', 'treated_at', 'recommended_by', 'recommended_at',
        'approved_by', 'approved_at',
    ];

    protected $casts = [
        'status' => DegreeVerificationStatus::class,
    ];

    public function payment()
    {
        return $this->hasOne(Payment4Degree::class, 'rrr', 'used_token');
    }
}
