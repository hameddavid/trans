<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficialApplication extends Model
{
    use HasFactory;
    protected $table = 'official_applications';
    protected $primaryKey = 'application_id';
    protected $casts = [ 'form_fields' => 'array', 'created_at' => 'datetime:m/d/Y'];
}
