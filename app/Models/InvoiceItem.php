<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'description',
        'details',
        'type',
        'quantity',
        'unit_price',
        'total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total = round($item->quantity * $item->unit_price, 2);
        });

        static::saved(function ($item) {
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotals();
        });
    }

    // ===== RELATIONS =====

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== SCOPES =====

    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ===== HELPERS =====

    public function isProduct(): bool
    {
        return $this->type === 'product';
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }

    public static function fromProduct(Product $product, float $quantity = 1): array
    {
        return [
            'product_id' => $product->id,
            'description' => $product->name,
            'details' => $product->short_description,
            'type' => $product->type,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'total' => round($quantity * $product->price, 2),
        ];
    }
}
