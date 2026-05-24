<?php
// app/Models/Treatment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Treatment extends Model
{
    protected $fillable = [
        'flock_id', 'diagnosis', 'product_name', 'active_ingredient',
        'dosage', 'administration_route', 'start_date', 'end_date',
        'withdrawal_days', 'withdrawal_end_date', 'batch_number',
        'animals_treated', 'cost', 'notes', 'prescribed_by'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'withdrawal_end_date' => 'date'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function prescriber()
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }
    
    // Accessors
    public function getDurationDaysAttribute()
    {
        if (!$this->end_date) return null;
        return $this->start_date->diffInDays($this->end_date);
    }
    
    public function getDaysUntilWithdrawalEndAttribute()
    {
        if (!$this->withdrawal_end_date) return null;
        $days = Carbon::today()->diffInDays($this->withdrawal_end_date, false);
        return $days > 0 ? $days : 0;
    }
    
    public function getIsWithdrawalActiveAttribute()
    {
        if (!$this->withdrawal_end_date) return false;
        return $this->withdrawal_end_date > Carbon::today();
    }
    
    // Scopes
    public function scopeActiveWithdrawal($query)
    {
        return $query->where('withdrawal_end_date', '>', now());
    }
    
    public function scopeExpiringWithdrawal($query, $days = 3)
    {
        return $query->whereBetween('withdrawal_end_date', [
            now(),
            now()->addDays($days)
        ]);
    }
}