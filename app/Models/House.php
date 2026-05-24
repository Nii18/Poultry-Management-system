<?php
// app/Models/House.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = [
        'name', 'house_code', 'species_id', 'capacity',
        'length_m', 'width_m', 'height_m', 'feeders_count',
        'drinkers_count', 'fans_count', 'heaters_count',
        'status', 'notes'
    ];
    
    protected $casts = [
        'capacity' => 'integer'
    ];
    
    // Relationships
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
    
    public function flocks(): HasMany
    {
        return $this->hasMany(Flock::class);
    }
    
    public function sensorDevices(): HasMany
    {
        return $this->hasMany(SensorDevice::class);
    }
    
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }
    
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
    // Accessors
    public function getAreaM2Attribute()
    {
        if ($this->length_m && $this->width_m) {
            return round($this->length_m * $this->width_m, 2);
        }
        return null;
    }
    
    public function getDensityAttribute()
    {
        if ($this->area_m2 && $this->capacity) {
            return round($this->capacity / $this->area_m2, 2);
        }
        return null;
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    
    public function scopeBySpecies($query, $speciesId)
    {
        return $query->where('species_id', $speciesId);
    }
}