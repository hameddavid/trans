<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentApplication extends Model
{
    use HasFactory;
    protected $table = 'student_applications';
    protected $casts = [
        'created_at' => 'datetime:m/d/Y'
    ];
}
