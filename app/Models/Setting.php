<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    const UPDATED_AT = null;

    protected $fillable = [
        'semester', 'session', 'status',
    ];
}
