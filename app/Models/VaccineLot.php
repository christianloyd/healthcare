<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VaccineLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'vaccine_name',
        'lot_number',
        'expiry_date',
        'quantity_received',
        'quantity_on_hand',
        'quantity_used',
        'low_stock_threshold',
        'received_date',
        'supplier',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'expiry_date'         => 'date',
        'received_date'       => 'date',
        'quantity_received'   => 'integer',
        'quantity_on_hand'    => 'integer',
        'quantity_used'       => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active'           => 'boolean',
    ];

    /* ----------------------------------------------------------
       Relationships
    ---------------------------------------------------------- */

    public function maternalImmunizations()
    {
        return $this->hasMany(MaternalImmunization::class, 'vaccine_lot_id');
    }

    /* ----------------------------------------------------------
       Accessors
    ---------------------------------------------------------- */

    /** Stock status: 'out-of-stock' | 'low-stock' | 'in-stock' */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity_on_hand <= 0) {
            return 'out-of-stock';
        }
        if ($this->quantity_on_hand <= $this->low_stock_threshold) {
            return 'low-stock';
        }
        return 'in-stock';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        $days = Carbon::now()->diffInDays($this->expiry_date, false);
        return $days <= 30 && $days > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_on_hand > 0
            && $this->quantity_on_hand <= $this->low_stock_threshold;
    }

    /* ----------------------------------------------------------
       Scopes
    ---------------------------------------------------------- */

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('quantity_on_hand', '>', 0);
    }

    public function scopeExpiringSoon($query)
    {
        return $query
            ->whereDate('expiry_date', '<=', Carbon::now()->addDays(30))
            ->whereDate('expiry_date', '>', Carbon::now());
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
                     ->where('quantity_on_hand', '>', 0);
    }

    /* ----------------------------------------------------------
       Business Logic
    ---------------------------------------------------------- */

    /**
     * Deduct one dose from this lot. Returns false if out of stock.
     */
    public function deductDose(): bool
    {
        if ($this->quantity_on_hand <= 0) {
            return false;
        }
        $this->decrement('quantity_on_hand');
        $this->increment('quantity_used');
        return true;
    }
}
