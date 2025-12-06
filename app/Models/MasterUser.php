<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class MasterUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'master_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin' || $this->isSuperAdmin();
    }

    /**
     * Check if user can manage sites.
     */
    public function canManageSites()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access monitoring.
     */
    public function canAccessMonitoring()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage backups.
     */
    public function canManageBackups()
    {
        return $this->isSuperAdmin();
    }
}








