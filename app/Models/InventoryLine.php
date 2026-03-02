<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLine extends Model
{
    protected $fillable = [
        'inventory_id',
        'product_id',
        'expected_quantity',
        'good_quantity',
        'damaged_quantity',
        'notes',
    ];

    protected $casts = [
        'expected_quantity' => 'integer',
        'good_quantity'     => 'integer',
        'damaged_quantity'  => 'integer',
    ];

    // ===== RELATIONS =====

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== HELPERS =====

    public function getCountedQuantityAttribute(): int
    {
        return ($this->good_quantity ?? 0) + ($this->damaged_quantity ?? 0);
    }

    public function getDiscrepancyAttribute(): int
    {
        return $this->counted_quantity - $this->expected_quantity;
    }

    public function isCounted(): bool
    {
        return $this->good_quantity !== null;
    }
}
