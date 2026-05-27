<?php
// app/Models/FarmProduce.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class FarmProduce extends Model
{
    protected $fillable = [
        'flock_id',
        'product_type',    // now free-text — no longer restricted to a fixed list
        'quantity',
        'quantity_damaged',
        'unit',
        'produce_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'produce_date'     => 'date',
        'quantity'         => 'decimal:2',
        'quantity_damaged' => 'decimal:2',
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

    public function getProductTypeLabelAttribute(): string
    {
        // Capitalise and humanise whatever the user typed
        return ucwords(str_replace('_', ' ', $this->product_type));
    }

    /**
     * Net quantity available = recorded - damaged
     */
    public function getNetQuantityAttribute(): float
    {
        return max(0, (float)$this->quantity - (float)$this->quantity_damaged);
    }

    // ── Dynamic product types (from actual records) ────────────────

    /**
     * Returns all unique product types that have ever been recorded.
     * This replaces the old hardcoded static array.
     */
    public static function getActiveProductTypes(): \Illuminate\Support\Collection
    {
        return self::select('product_type')
            ->distinct()
            ->orderBy('product_type')
            ->pluck('product_type');
    }

    /**
     * Returns product types with their total quantities for the current month.
     * Used for the stat cards.
     */
    public static function getMonthlyStats(): \Illuminate\Support\Collection
    {
        return self::select(
                'product_type',
                DB::raw('SUM(quantity) as total_produced'),
                DB::raw('SUM(quantity_damaged) as total_damaged'),
                DB::raw('SUM(quantity - quantity_damaged) as total_available'),
                DB::raw('COUNT(*) as record_count')
            )
            ->whereMonth('produce_date', now()->month)
            ->whereYear('produce_date', now()->year)
            ->groupBy('product_type')
            ->get();
    }

    /**
     * Returns ALL-TIME totals per product type.
     */
    public static function getAllTimeStats(): \Illuminate\Support\Collection
    {
        return self::select(
                'product_type',
                DB::raw('SUM(quantity) as total_produced'),
                DB::raw('SUM(quantity_damaged) as total_damaged'),
                DB::raw('SUM(quantity - quantity_damaged) as total_available')
            )
            ->groupBy('product_type')
            ->get()
            ->keyBy('product_type');
    }

    /**
     * Default unit suggestions for common product types.
     * Falls back to 'units' for anything not listed.
     */
    public static function defaultUnit(string $productType): string
    {
        $map = [
            'eggs'           => 'pieces',
            'milk'           => 'litres',
            'live_bird'      => 'birds',
            'meat'           => 'kg',
            'breeding_stock' => 'birds',
            'manure'         => 'bags',
            'wool'           => 'kg',
            'honey'          => 'kg',
        ];

        return $map[strtolower($productType)] ?? 'units';
    }

    /**
     * Emoji icons for known product types (display only).
     */
    public static function productIcon(string $productType): string
    {
        $icons = [
            'eggs'           => '🥚',
            'milk'           => '🥛',
            'live_bird'      => '🐓',
            'meat'           => '🍗',
            'breeding_stock' => '🧬',
            'manure'         => '💩',
            'wool'           => '🧶',
            'honey'          => '🍯',
        ];

        return $icons[strtolower($productType)] ?? '📦';
    }
}