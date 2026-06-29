<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BreedingRecord extends Model
{
    protected $fillable = [
        'flock_id', 'mate_id',
        'female_breeder_count',   // snapshot at creation time
        'male_breeder_count',     // snapshot at creation time (null for AI)
        'breeding_date', 'expected_delivery_date',
        'actual_delivery_date', 'breeding_method', 'is_successful',
        'offspring_count', 'stillborn_count', 'weaned_count', 'notes', 'recorded_by',
    ];

    protected $casts = [
        'breeding_date'          => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date'   => 'date',
        'is_successful'          => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Compute the effective breeding population for a given flock at the
     * moment a breeding record is about to be created.
     *
     * Rules:
     *   1. If the flock has no sex set (mixed flock)  → use current_count
     *   2. If the flock has no breeder_count set (= 0) → use current_count
     *   3. Otherwise                                   → use breeder_count
     *
     * This is a static helper so it can be called from the controller
     * before the record is persisted.
     */
    public static function resolveEffectiveBreeders(Flock $flock): array
    {
        $usingWholeFloc = is_null($flock->sex) || $flock->breeder_count === 0;

        return [
            'effective_count' => $usingWholeFloc ? $flock->current_count : $flock->breeder_count,
            'mode'            => $usingWholeFloc ? 'whole_flock' : 'breeder_subset',
        ];
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * How many females actually participated in this breeding.
     * Falls back to the female flock's current_count if not snapshotted
     * (covers legacy records created before this column existed).
     */
    public function getEffectiveFemalBreedersAttribute(): int
    {
        if (!is_null($this->female_breeder_count)) {
            return $this->female_breeder_count;
        }
        return $this->female?->current_count ?? 0;
    }

    /**
     * How many males actually participated. NULL for AI records.
     */
    public function getEffectiveMaleBreedersAttribute(): ?int
    {
        if (!is_null($this->male_breeder_count)) {
            return $this->male_breeder_count;
        }
        return $this->male?->current_count ?? null;
    }

    /**
     * Binary conception flag: 100 if successful, 0 if not.
     * A true per-animal rate requires insemination attempt tracking
     * which is out of scope for now.
     */
    public function getConceptionRateAttribute(): int
    {
        return $this->is_successful ? 100 : 0;
    }

    public function getLiveBirthRateAttribute(): float
    {
        if (!$this->offspring_count) return 0.0;
        $liveBirths = $this->offspring_count - ($this->stillborn_count ?? 0);
        return round(($liveBirths / $this->offspring_count) * 100, 2);
    }

    /**
     * Clamped to [0, 100] — prevents weaned_count > offspring_count
     * from producing a rate above 100%.
     */
    public function getWeaningRateAttribute(): float
    {
        if (!$this->offspring_count) return 0.0;
        $rate = ($this->weaned_count ?? 0) / $this->offspring_count * 100;
        return min(100.0, round($rate, 2));
    }

    /**
     * Offspring born per female breeder.
     * Meaningful once actual_delivery_date is set.
     */
    public function getOffspringPerFemaleAttribute(): float
    {
        $females = $this->effective_femal_breeders;
        if (!$this->offspring_count || !$females) return 0.0;
        return round($this->offspring_count / $females, 2);
    }

    /**
     * Offspring born per male breeder.
     * NULL for AI records (no male flock).
     */
    public function getOffspringPerMaleAttribute(): ?float
    {
        $males = $this->effective_male_breeders;
        if (is_null($males) || !$this->offspring_count || $males === 0) return null;
        return round($this->offspring_count / $males, 2);
    }

    /**
     * Male-to-female breeder ratio (e.g. 1:5 → returns 0.2).
     * NULL for AI records.
     */
    public function getMaleToFemaleRatioAttribute(): ?float
    {
        $males   = $this->effective_male_breeders;
        $females = $this->effective_femal_breeders;
        if (is_null($males) || !$females || $males === 0) return null;
        return round($males / $females, 3);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

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