<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReturnItem extends Model
{
    protected $fillable = [
        'delivery_return_id',
        'product_id',
        'description',
        'quantity_returned',
        'unit_price',
        'unit',
        'sort_order',
    ];

    protected $casts = [
        'quantity_returned' => 'decimal:2',
        'unit_price'        => 'decimal:2',
    ];

    public function deliveryReturn(): BelongsTo
    {
        return $this->belongsTo(DeliveryReturn::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getLineTotalAttribute(): float
    {
        return (float) $this->quantity_returned * (float) ($this->unit_price ?? 0);
    }
}
