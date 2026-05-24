<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'farm_name',
        'is_active',
        'avatar',
        'last_login_at',
        'last_seen_at',
        'last_activity_at',
        'current_session_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_MANAGER = 'manager';
    const ROLE_WORKER = 'worker';
    const ROLE_VETERINARIAN = 'veterinarian';
    const ROLE_ACCOUNTANT = 'accountant';

    /**
     * Get all available roles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN => 'Admin - Full System Control',
            self::ROLE_MANAGER => 'Farm Manager - Operational Management',
            self::ROLE_WORKER => 'Farm Worker - Field Operations',
            self::ROLE_VETERINARIAN => 'Veterinarian - Health Management',
            self::ROLE_ACCOUNTANT => 'Accountant - Financial Management',
        ];
    }

    // ==================== ROLE CHECK METHODS ====================
    
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManager()
    {
        return $this->role === self::ROLE_MANAGER || $this->isAdmin();
    }

    public function isWorker()
    {
        return $this->role === self::ROLE_WORKER;
    }

    public function isVeterinarian()
    {
        return $this->role === self::ROLE_VETERINARIAN || $this->isAdmin();
    }

    public function isAccountant()
    {
        return $this->role === self::ROLE_ACCOUNTANT || $this->isAdmin();
    }

    // ==================== AVATAR METHODS ====================
    
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/avatars/' . $this->avatar);
        }
        
        return asset('assets/images/genericavatarimage.jpg');
    }

    // ==================== ACTIVITY TRACKING METHODS ====================
    
    public function updateLastSeen()
    {
        $this->last_seen_at = now();
        $this->save();
    }

    public function updateLastLogin()
    {
        $this->last_login_at = now();
        $this->save();
    }

    public function isOnline()
    {
        if (!$this->last_seen_at) {
            return false;
        }
        return $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function getOnlineStatus()
    {
        if (!$this->last_seen_at) {
            return ['text' => '⚫ Never Seen', 'class' => 'bg-secondary'];
        }
        
        $minutesAgo = $this->last_seen_at->diffInMinutes(now());
        
        if ($minutesAgo < 5) {
            return ['text' => '🟢 Online Now', 'class' => 'bg-success'];
        } elseif ($minutesAgo < 30) {
            return ['text' => '🟡 Active Recently', 'class' => 'bg-info'];
        } elseif ($minutesAgo < 120) {
            return ['text' => '🟠 Away', 'class' => 'bg-warning'];
        } else {
            return ['text' => '⚫ Offline', 'class' => 'bg-secondary'];
        }
    }

    public function getWeeklyLoginCount()
    {
        if (!$this->last_login_at) {
            return 0;
        }
        
        return $this->last_login_at->greaterThan(now()->startOfWeek()) ? 1 : 0;
    }

    // ==================== USER SWITCHING METHODS ====================
    
    /**
     * Check if user can switch to another user
     */
    public function canSwitchTo(User $targetUser)
    {
        // Cannot switch to yourself
        if ($this->id === $targetUser->id) {
            return false;
        }
        
        // Admin can switch to anyone
        if ($this->isAdmin()) {
            return true;
        }
        
        // Manager can only switch to workers
        if ($this->isManager()) {
            return $targetUser->isWorker();
        }
        
        return false;
    }

    /**
     * Get switchable users based on role
     */
    public function getSwitchableUsers()
    {
        if ($this->isAdmin()) {
            // Admin sees all users except themselves
            return User::where('id', '!=', $this->id)->get();
        }
        
        if ($this->isManager()) {
            // Manager only sees workers
            return User::where('role', self::ROLE_WORKER)
                ->where('id', '!=', $this->id)
                ->get();
        }
        
        // Other roles see no one
        return collect();
    }

    // ==================== PERMISSION METHODS ====================
    
    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    public function canManageSpecies()
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageHouses()
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageFlocks()
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canCreateDailyLogs()
    {
        return $this->isWorker();
    }

    public function canEditDailyLogs()
    {
        return $this->isManager();
    }

    public function canDeleteDailyLogs()
    {
        return $this->isAdmin();
    }

    public function canManageTreatments()
    {
        return $this->isVeterinarian();
    }

    public function canManageFeed()
    {
        return $this->isAdmin() || $this->isManager();
    }

    public function canManageExpenses()
    {
        return $this->isAccountant();
    }

    public function canViewReports()
    {
        return $this->isAdmin() || $this->isManager() || $this->isAccountant() || $this->isVeterinarian();
    }

    public function canAccessSettings()
    {
        return $this->isAdmin();
    }
}