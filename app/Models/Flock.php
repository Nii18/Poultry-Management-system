<?php
// app/Models/Flock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Flock extends Model
{
    protected $fillable = [
        'species_id', 'house_id', 'flock_number', 'breed_variety',
        'start_date', 'initial_count', 'current_count', 'source',
        'production_type', 'is_breeding_stock', 'parity_number',
        'last_breeding_date', 'expected_delivery_date', 'status',
        'end_date', 'final_count', 'total_weight_kg',
        'average_price_per_kg', 'total_revenue', 'notes', 'created_by'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'last_breeding_date' => 'date',
        'expected_delivery_date' => 'date',
        'is_breeding_stock' => 'boolean'
    ];
    
    // Relationships
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
    
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
    
    public function dailyLogs(): HasMany
    {
        return $this->hasMany(DailyLog::class);
    }
    
    public function feedIssuances(): HasMany
    {
        return $this->hasMany(FeedIssuance::class);
    }
    
    public function vaccinations(): HasMany
    {
        return $this->hasMany(Vaccination::class);
    }
    
    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }
    
    public function healthRecords(): HasMany
    {
        return $this->hasMany(HealthRecord::class);
    }
    
    public function breedingRecords(): HasMany
    {
        return $this->hasMany(BreedingRecord::class);
    }
    
    public function performanceMetrics(): HasMany
    {
        return $this->hasMany(PerformanceMetric::class);
    }
    
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Accessors
    public function getAgeInDaysAttribute()
    {
        $referenceDate = $this->status === 'closed' && $this->end_date 
            ? $this->end_date 
            : Carbon::today();
            
        return $this->start_date->diffInDays($referenceDate);
    }
    
    public function getAgeInWeeksAttribute()
    {
        return floor($this->age_in_days / 7);
    }
    
    public function getTotalMortalityAttribute()
    {
        return $this->dailyLogs()->sum('mortality_count') + 
               $this->dailyLogs()->sum('culling_count');
    }
    
    public function getMortalityRateAttribute()
    {
        if ($this->initial_count == 0) return 0;
        return round(($this->total_mortality / $this->initial_count) * 100, 2);
    }
    
    public function getCurrentCountAttribute()
    {
        return $this->initial_count - $this->total_mortality;
    }
    
    public function getTotalFeedConsumedAttribute()
    {
        return $this->dailyLogs()->sum('feed_intake_kg');
    }
    
    public function getAverageDailyGainAttribute()
    {
        $latestLog = $this->dailyLogs()
            ->whereNotNull('average_weight_kg')
            ->latest('log_date')
            ->first();
            
        if (!$latestLog || $this->age_in_days == 0) return 0;
        
        return round($latestLog->average_weight_kg / $this->age_in_days, 3);
    }
    
    public function getFeedConversionRatioAttribute()
    {
        $latestLog = $this->dailyLogs()
            ->whereNotNull('average_weight_kg')
            ->latest('log_date')
            ->first();
            
        if (!$latestLog) return 0;
        
        $totalWeightGained = $latestLog->average_weight_kg * $this->current_count;
        $initialWeight = $this->getInitialWeight();
        $totalWeightGain = $totalWeightGained - ($initialWeight * $this->initial_count);
        
        if ($totalWeightGain <= 0) return 0;
        
        return round($this->total_feed_consumed / $totalWeightGain, 2);
    }
    
    protected function getInitialWeight()
    {
        return match($this->species->code) {
            'CH' => 0.045, // 45g day-old chick
            'PG' => 1.5,   // 1.5kg weaner pig
            'CT' => 40,    // 40kg calf
            'RB' => 0.05,  // 50g kit
            'GT' => 3,     // 3kg kid
            default => 0
        };
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
    
    public function scopeMeatProduction($query)
    {
        return $query->where('production_type', 'meat');
    }
    
    public function scopeEggProduction($query)
    {
        return $query->where('production_type', 'eggs');
    }
    
    public function scopeBreedingStock($query)
    {
        return $query->where('is_breeding_stock', true);
    }
}