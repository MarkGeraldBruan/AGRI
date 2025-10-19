<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StockMovement extends Model
{
    protected $fillable = [
        'supply_id',
        'type',
        'quantity',
        'balance_after',
        'reference',
        'notes',
        'office_description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the supply that owns the stock movement
     */
    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supplies::class);
    }

    /**
     * Generate RIS reference number
     */
    public static function generateReference(): string
    {
        $now = now();
        $year = $now->format('Y');
        $month = $now->format('m');
        
        // Get the count of movements for this month
        $count = static::whereYear('created_at', $year)
                      ->whereMonth('created_at', $month)
                      ->count();
        
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        
        return "RIS #{$year}-{$month}-{$sequence}";
    }

    /**
     * Scope for a specific supply
     */
    public function scopeForSupply($query, $supplyId)
    {
        return $query->where('supply_id', $supplyId);
    }

    /**
     * Scope ordered by date
     */
    public function scopeOrderedByDate($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
}