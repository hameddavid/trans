<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursePassMark extends Model
{
    protected $table = 'ug_course_with_pass_mark';

    public $timestamps = false;

    protected $fillable = [
        'course_code', 'pass_mark', 'programme', 'deleted',
    ];
}
