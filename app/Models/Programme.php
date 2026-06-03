<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    protected $table = 't_programmes';
    protected $primaryKey = 'programme_id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['programme_id', 'programme', 'department_id_FK'];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id_FK', 'department_id');
    }
}
