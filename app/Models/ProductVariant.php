<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'options',
        'price',
        'compare_at_price',
        'stock_quantity',
        'image_path',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'stock_quantity' => 'decimal:3',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ===== RELATIONS =====

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    // ===== HELPERS =====

    public function getImageUrlAttribute(): ?string
    {
        $path = $this->image_path ?? $this->product->main_image_path;
        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * Obtenir le prix effectif (prix variant ou prix produit)
     */
    public function getEffectivePrice(): float
    {
        return $this->price ?? $this->product->price;
    }

    /**
     * Obtenir le prix barré effectif
     */
    public function getEffectiveCompareAtPrice(): ?float
    {
        return $this->compare_at_price ?? $this->product->compare_at_price;
    }

    public function isInStock(): bool
    {
        if (!$this->product->track_inventory) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    public function hasDiscount(): bool
    {
        $comparePrice = $this->getEffectiveCompareAtPrice();
        return $comparePrice && $comparePrice > $this->getEffectivePrice();
    }

    /**
     * Obtenir les options formatées (ex: "Couleur: Rouge, Taille: M")
     */
    public function getFormattedOptions(): string
    {
        if (empty($this->options)) {
            return '';
        }

        $formatted = [];
        foreach ($this->options as $key => $value) {
            $formatted[] = ucfirst($key) . ': ' . $value;
        }

        return implode(', ', $formatted);
    }

    public function decrementStock(float $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    public function incrementStock(float $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }
}
