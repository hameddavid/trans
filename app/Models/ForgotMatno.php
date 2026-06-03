<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForgotMatno extends Model
{
    protected $table = 'forgot_matno';

    protected $fillable = [
        'surname', 'firstname', 'othername', 'email', 'phone',
        'program', 'date_left', 'matno_found', 'status',
        'treated_by', 'treated_at',
    ];
}
