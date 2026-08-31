<?php

namespace App\Models;

use App\Enums\AdminRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'admin';

    protected $fillable = [
        'staff_id', 'surname', 'firstname', 'othername', 'phone', 'email',
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
        return $this->role === AdminRole::APPROVER || $this->role === AdminRole::SUPER_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === AdminRole::SUPER_ADMIN;
    }
}
