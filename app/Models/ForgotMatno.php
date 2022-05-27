<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForgotMatno extends Model
{
    use HasFactory;
    protected $table = 'forgot_matno';
  //  protected $casts = [ 'matno_found' => 'array', ];
}
