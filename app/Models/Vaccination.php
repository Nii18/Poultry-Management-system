<?php
// app/Models/Vaccination.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vaccination extends Model
{
    protected $fillable = [
        'flock_id', 'vaccine_name', 'disease_target', 'day_administered',
        'administration_date', 'route', 'batch_number', 'expiry_date',
        'dosage_ml', 'birds_vaccinated', 'administered_by', 'notes'
    ];
    
    protected $casts = [
        'administration_date' => 'date',
        'expiry_date' => 'date'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function administrator()
    {
        return $this->belongsTo(User::class, 'administered_by');
    }
    
    // Accessors
    public function getCoveragePercentageAttribute()
    {
        if (!$this->birds_vaccinated || !$this->flock->current_count) return null;
        return round(($this->birds_vaccinated / $this->flock->current_count) * 100, 2);
    }
    
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date < now();
    }

    
}