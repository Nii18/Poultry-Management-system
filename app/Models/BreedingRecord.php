<?php
// app/Models/BreedingRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreedingRecord extends Model
{
    protected $fillable = [
        'flock_id', 'mate_id', 'breeding_date', 'expected_delivery_date',
        'actual_delivery_date', 'breeding_method', 'is_successful',
        'offspring_count', 'stillborn_count', 'weaned_count', 'notes', 'recorded_by'
    ];
    
    protected $casts = [
        'breeding_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'is_successful' => 'boolean'
    ];
    
    // Relationships
    public function female(): BelongsTo
    {
        return $this->belongsTo(Flock::class, 'flock_id');
    }
    
    public function male(): BelongsTo
    {
        return $this->belongsTo(Flock::class, 'mate_id');
    }
    
    public function offspringRecords(): HasMany
    {
        return $this->hasMany(OffspringRecord::class);
    }
    
    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
    
    // Accessors
    public function getConceptionRateAttribute()
    {
        if (!$this->is_successful) return 0;
        return 100;
    }
    
    public function getLiveBirthRateAttribute()
    {
        if (!$this->offspring_count) return 0;
        $liveBirths = $this->offspring_count - $this->stillborn_count;
        return round(($liveBirths / $this->offspring_count) * 100, 2);
    }
    
    public function getWeaningRateAttribute()
    {
        if (!$this->offspring_count) return 0;
        return round(($this->weaned_count / $this->offspring_count) * 100, 2);
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('expected_delivery_date', '>', now())
                     ->whereNull('actual_delivery_date');
    }
    
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }
}