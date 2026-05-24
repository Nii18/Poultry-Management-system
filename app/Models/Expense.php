<?php
// app/Models/Expense.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'flock_id', 'house_id', 'category', 'description', 'amount',
        'expense_date', 'payment_method', 'receipt_number',
        'vendor_name', 'notes', 'created_by'
    ];
    
    protected $casts = [
        'expense_date' => 'date'
    ];
    
    // Relationships
    public function flock(): BelongsTo
    {
        return $this->belongsTo(Flock::class);
    }
    
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('expense_date', [$start, $end]);
    }
}