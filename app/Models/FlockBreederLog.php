<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlockBreederLog extends Model
{
    protected $fillable = [
        'flock_id',
        'breeder_count',
        'sellable_count',
        'reason',
        'set_by',
    ];

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function setter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}