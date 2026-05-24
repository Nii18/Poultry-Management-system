<?php
// app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'flock_id', 'type', 'title', 'message',
        'severity', 'data', 'read_at', 'sent_at', 'created_by'
    ];
    
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime'
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Accessors
    public function getIsReadAttribute()
    {
        return !is_null($this->read_at);
    }
    
    // Role-based scope - Users see only notifications they're allowed to see
    public function scopeForUser($query, $user)
    {
        $role = $user->role;
        
        switch ($role) {
            case 'admin':
                // Admin sees all notifications
                return $query;
                
            case 'manager':
                // Manager sees: management, worker, financial, operational, health notifications
                return $query->whereIn('type', [
                    'management', 'worker', 'financial', 'operational', 'health', 
                    'task', 'attendance', 'expense', 'treatment', 'vaccination'
                ]);
                
            case 'accountant':
                // Accountant sees: financial, expense, sales, payroll
                return $query->whereIn('type', ['financial', 'expense', 'sales', 'payroll']);
                
            case 'veterinarian':
                // Veterinarian sees: health, treatment, vaccination, disease
                return $query->whereIn('type', ['health', 'treatment', 'vaccination', 'disease']);
                
            case 'worker':
            case 'head_worker':
                // Worker sees: task, attendance, daily_log, operational, flock
                return $query->whereIn('type', ['task', 'attendance', 'daily_log', 'operational', 'flock']);
                
            default:
                return $query->where('user_id', $user->id);
        }
    }
    
    // Scopes
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
    
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
    
    public function scopeRecent($query, $limit = 10)
    {
        return $query->latest()->limit($limit);
    }
}