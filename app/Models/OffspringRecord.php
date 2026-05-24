<?php
// app/Models/OffspringRecord.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffspringRecord extends Model
{
    protected $fillable = [
        'breeding_record_id', 'new_flock_id', 'count',
        'average_birth_weight_kg', 'ear_tag_prefix',
        'ear_tag_start_number', 'notes'
    ];
    
    // Relationships
    public function breedingRecord(): BelongsTo
    {
        return $this->belongsTo(BreedingRecord::class);
    }
    
    public function newFlock(): BelongsTo
    {
        return $this->belongsTo(Flock::class, 'new_flock_id');
    }
}