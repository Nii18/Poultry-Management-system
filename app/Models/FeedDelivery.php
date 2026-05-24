<?php
// app/Models/FeedDelivery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedDelivery extends Model
{
    protected $fillable = [
        'feed_type_id', 'supplier_name', 'invoice_number', 'quantity_kg',
        'cost_per_kg', 'total_cost', 'delivery_date', 'expiry_date',
        'remaining_quantity_kg', 'batch_number', 'notes', 'received_by'
    ];
    
    protected $casts = [
        'delivery_date' => 'date',
        'expiry_date' => 'date'
    ];
    
    // Relationships
    public function feedType(): BelongsTo
    {
        return $this->belongsTo(FeedType::class);
    }
    
    public function feedIssuances(): HasMany
    {
        return $this->hasMany(FeedIssuance::class);
    }
    
    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
    
    // Accessors
    public function getUsagePercentageAttribute()
    {
        $used = $this->quantity_kg - $this->remaining_quantity_kg;
        return round(($used / $this->quantity_kg) * 100, 2);
    }
    
    // Scopes
    public function scopeNotExpired($query)
    {
        return $query->where('expiry_date', '>', now());
    }
    
    public function scopeLowStock($query, $threshold = 500)
    {
        return $query->where('remaining_quantity_kg', '<', $threshold);
    }
}