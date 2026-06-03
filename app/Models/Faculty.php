<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 't_colleges';
    protected $primaryKey = 'college_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['college_id', 'college'];

    public function departments()
    {
        return $this->hasMany(Department::class, 'college_id_FK', 'college_id');
    }
}
