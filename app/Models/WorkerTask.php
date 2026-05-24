<?php
// app/Models/WorkerTask.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerTask extends Model
{
    protected $table = 'worker_tasks';
    
    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'start_time',
        'end_time',
        'assigned_to',
        'assigned_by',
        'completed_at',
        'is_recurring',
        'recurring_pattern'
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'is_recurring' => 'boolean'
    ];
    
    // Relationships
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    public function scopeForToday($query)
    {
        return $query->whereDate('due_date', today());
    }
    
    public function scopeForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }
    
    // Accessors
    public function getPriorityBadgeAttribute()
    {
        $colors = [
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info'
        ];
        
        return '<span class="badge bg-' . ($colors[$this->priority] ?? 'secondary') . '-soft text-' . ($colors[$this->priority] ?? 'secondary') . '">
                    ' . ucfirst($this->priority) . '
                </span>';
    }
    
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'in_progress' => 'primary',
            'completed' => 'success'
        ];
        
        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '-soft text-' . ($colors[$this->status] ?? 'secondary') . '">
                    ' . ucfirst(str_replace('_', ' ', $this->status)) . '
                </span>';
    }
}