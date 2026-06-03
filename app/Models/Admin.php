<?php

namespace App\Models;

use App\Enums\AdminRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'surname', 'firstname', 'othername', 'phone', 'email',
        'password', 'role', 'account_status', 'title',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'role' => AdminRole::class,
    ];

    public function isRecommender(): bool
    {
        return $this->role === AdminRole::RECOMMENDER;
    }

    public function isApprover(): bool
    {
        return $this->role === AdminRole::APPROVER;
    }
}
