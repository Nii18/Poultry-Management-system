<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmProduce extends Model
{
    protected $fillable = [
        'flock_id',
        'product_type',
        'quantity',
        'unit',
        'produce_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'produce_date' => 'date',
        'quantity'     => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ──────────────────────────────────────────────────

    /**
     * Human-readable product type label.
     */
    public function getProductTypeLabelAttribute(): string
    {
        return self::productTypeLabels()[$this->product_type] ?? ucfirst($this->product_type);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Canonical list of product types with their display labels.
     * Keep in sync with Sale::productTypeLabels() if that exists.
     */
    public static function productTypeLabels(): array
    {
        return [
            'eggs'            => 'Eggs',
            'live_bird'       => 'Live Bird',
            'meat'            => 'Meat',
            'breeding_stock'  => 'Breeding Stock',
            'manure'          => 'Manure',
        ];
    }

    /**
     * Default unit for a given product type.
     */
    public static function defaultUnit(string $productType): string
    {
        return [
            'eggs'           => 'pieces',
            'live_bird'      => 'birds',
            'meat'           => 'kg',
            'breeding_stock' => 'birds',
            'manure'         => 'bags',
        ][$productType] ?? 'pieces';
    }
}