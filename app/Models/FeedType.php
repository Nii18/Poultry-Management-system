<?php
// app/Models/FeedType.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedType extends Model
{
    protected $fillable = [
        'species_id', 'name', 'code', 'category',
        'protein_percentage', 'energy_mj_kg', 'description', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    // Relationships
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
    
    public function feedDeliveries(): HasMany
    {
        return $this->hasMany(FeedDelivery::class);
    }
    
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}