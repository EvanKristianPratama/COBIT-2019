<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable; //, HasRoles;

    protected $fillable = [
        'id', 'sso_user_id', 'name', 'email', 'password', 'organisasi', 'jabatan', 'role', 'isActivated', 'approval_status',
    ];

    protected $casts = [
        'isActivated' => 'boolean',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the user's full name (for display purposes).
     */
    public function getFullNameAttribute()
    {
        return ucfirst($this->name);
    }

    /**
     * Get all evaluations created by this user
     */
    public function evaluations()
    {
        return $this->hasMany(MstEval::class, 'user_id', 'id');
    }
}
