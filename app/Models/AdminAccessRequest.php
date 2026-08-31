<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminAccessRequest extends Model
{
    protected $fillable = [
        'email', 'staff_name', 'title', 'department', 'staff_id', 'status',
    ];
}
