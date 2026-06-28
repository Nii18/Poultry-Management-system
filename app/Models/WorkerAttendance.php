<?php
// app/Models/WorkerAttendance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerAttendance extends Model
{
    protected $table = 'worker_attendance';
    
    protected $fillable = [
        'user_id',
        'date',
        'window',
        'clock_in',
        'clock_out',
        'hours_worked',
        'status',
        'notes',
        'approved_by',
        'approved_at'
    ];
    
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'string',   
        'clock_out' => 'string', 
        'approved_at' => 'datetime'
    ];
    
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
    
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }
    
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }
    
    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }
    
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * A session that's been clocked in but not yet clocked out. Since a
     * worker can have several sessions per day now, "currently clocked in"
     * means "has an open session", not "has a row for today" — there may
     * also be earlier, already-closed sessions for the same day.
     */
    public function scopeOpenSession($query)
    {
        return $query->whereNotNull('clock_in')->whereNull('clock_out');
    }
    
    // Accessors
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'half_day' => 'info',
            'holiday' => 'secondary'
        ];
        
        return '<span class="badge bg-' . ($colors[$this->status] ?? 'secondary') . '-soft text-' . ($colors[$this->status] ?? 'secondary') . '">
                    ' . ucfirst(str_replace('_', ' ', $this->status)) . '
                </span>';
    }

    /**
     * Human-readable window label for display, e.g. in the attendance
     * history table. Null window (clocked in outside all three task
     * windows) renders as a plain dash rather than blank.
     */
    public function getWindowLabelAttribute(): string
    {
        return $this->window ? ucfirst($this->window) : '—';
    }
    
       public function getFormattedClockInAttribute()
    {
    return $this->clock_in 
        ? \Carbon\Carbon::createFromFormat('H:i:s', $this->clock_in)->format('h:i A') 
        : '--:--';
    }
    
    public function getFormattedClockOutAttribute()
    { 
    return $this->clock_out 
        ? \Carbon\Carbon::createFromFormat('H:i:s', $this->clock_out)->format('h:i A') 
        : '--:--';
    }
}