<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeDept extends Model
{
    protected $table = 't_college_dept';

    public $timestamps = false;

    protected $fillable = ['prog_code', 'programme', 'department', 'college'];
}
