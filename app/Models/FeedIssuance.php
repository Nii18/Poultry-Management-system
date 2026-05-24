<?php
// app/Models/FeedIssuance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedIssuance extends Model
{
    protected $fillable = [
        'flock_id', 'feed_delivery_id', 'quantity_kg',
        'issuance_date', 'issuance_time', 'issued_by', 'notes'
    ];
    
    protected $casts = [
        'issuance_date' => 'date',
        'issuance_time' => 'datetime'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function feedDelivery(): BelongsTo
    {
        return $this->belongsTo(FeedDelivery::class);
    }
    
    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}