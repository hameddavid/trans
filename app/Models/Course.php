<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table = 't_course';

    public $timestamps = false;

    protected $fillable = [
        'course_code', 'course_title', 'unit', 'unit_id',
    ];
}
