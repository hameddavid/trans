<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Applicant extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = ['surname','firstname','email','password','mobile'];
    
}
