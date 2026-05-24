<?php
// app/Models/HealthRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthRecord extends Model
{
    protected $fillable = [
        'flock_id', 'record_type', 'condition', 'symptoms',
        'lab_results', 'veterinarian_notes', 'affected_count',
        'severity', 'record_date', 'recorded_by'
    ];
    
    protected $casts = [
        'symptoms' => 'array',
        'lab_results' => 'array',
        'record_date' => 'date'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
    
    // Accessors
    public function getAffectedPercentageAttribute()
    {
        if (!$this->affected_count || !$this->flock->current_count) return null;
        return round(($this->affected_count / $this->flock->current_count) * 100, 2);
    }
    
    // Scopes
    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
    
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }
}