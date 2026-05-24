<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use SoftDeletes;

    protected $table = 'sales';

    protected $fillable = [
        'flock_id',
        'product_type',
        'quantity',
        'unit_price',
        'total_amount',
        'sale_date',
        'customer_name',
        'payment_method',
        'receipt_number',
        'description',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function flock()
    {
        return $this->belongsTo(Flock::class, 'flock_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '₵' . number_format($this->total_amount, 2);
    }

    public function getProductTypeLabelAttribute()
    {
        $labels = [
            'eggs_tray' => 'Eggs (Tray)',
            'eggs_crate' => 'Eggs (Crate)',
            'eggs_box' => 'Eggs (Box)',
            'live_bird' => 'Live Bird',
            'meat_kg' => 'Meat (kg)',
            'breeding_stock' => 'Breeding Stock',
            'manure' => 'Manure',
            'other' => 'Other'
        ];
        return $labels[$this->product_type] ?? ucfirst($this->product_type);
    }
}