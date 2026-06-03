<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 't_departments';
    protected $primaryKey = 'department_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['department_id', 'department', 'college_id_FK'];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class, 'college_id_FK', 'college_id');
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class, 'department_id_FK', 'department_id');
    }
}
