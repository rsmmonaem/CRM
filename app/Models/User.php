<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get leads assigned to this user
     */
    public function assignedLeads()
    {
        return $this->hasMany(Lead::class, 'assigned_user_id');
    }

    /**
     * Get leads created by this user
     */
    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    /**
     * Get lead details created by this user
     */
    public function createdLeadDetails()
    {
        return $this->hasMany(LeadDetail::class, 'created_by');
    }

    /**
     * Get services created by this user
     */
    public function createdServices()
    {
        return $this->hasMany(Service::class, 'created_by');
    }

    /**
     * Get statuses created by this user
     */
    public function createdStatuses()
    {
        return $this->hasMany(Status::class, 'created_by');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function hasPermission($module, $action)
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    public function hasModulePermission($module)
    {
        // Admin has all permissions
        if ($this->isAdmin()) {
            return true;
        }

        return $this->permissions()
            ->where('module', $module)
            ->exists();
    }

    public function getPermissionsByModule()
    {
        return $this->permissions()
            ->get()
            ->groupBy('module')
            ->map(function ($permissions) {
                return $permissions->pluck('action')->toArray();
            });
    }
}
