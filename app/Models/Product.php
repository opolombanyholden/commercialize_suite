<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'type',
        'short_description',
        'description',
        'price',
        'cost_price',
        'compare_at_price',
        'tax_id',
        'track_inventory',
        'stock_quantity',
        'stock_alert_threshold',
        'unit',
        'main_image_path',
        'is_published_online',
        'share_title',
        'share_description',
        'share_image_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'has_variants',
        'is_active',
        'is_featured',
        'sort_order',
        'views_count',
        'sales_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'stock_alert_threshold' => 'integer',
        'track_inventory' => 'boolean',
        'is_published_online' => 'boolean',
        'has_variants' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'views_count' => 'integer',
        'sales_count' => 'integer',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = static::generateSku($product->company_id);
            }
        });
    }

    /**
     * Generate unique SKU
     */
    public static function generateSku(int $companyId): string
    {
        $prefix = 'PRD';
        $timestamp = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$timestamp}-{$random}";
    }

    // ===== RELATIONS =====

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function inventoryLines(): HasMany
    {
        return $this->hasMany(InventoryLine::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublishedOnline($query)
    {
        return $query->where('is_published_online', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeProducts($query)
    {
        return $query->where('type', 'product');
    }

    public function scopeServices($query)
    {
        return $query->where('type', 'service');
    }

    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereNotNull('stock_alert_threshold')
            ->whereColumn('stock_quantity', '<=', 'stock_alert_threshold');
    }

    /**
     * Produits disponibles à la vente : services + produits sans suivi stock + produits en stock
     */
    public function scopeAvailableForSale($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
              ->orWhere('type', 'service')
              ->orWhere(function ($q2) {
                  $q2->where('track_inventory', true)
                     ->where('stock_quantity', '>', 0);
              });
        });
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('sku', 'like', "%{$search}%")
              ->orWhere('barcode', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // ===== HELPERS =====

    public function getMainImageUrlAttribute(): ?string
    {
        return $this->main_image_path ? asset('storage/' . $this->main_image_path) : null;
    }

    public function getShareImageUrlAttribute(): ?string
    {
        $path = $this->share_image_path ?? $this->main_image_path;
        return $path ? asset('storage/' . $path) : null;
    }

    public function isService(): bool
    {
        return $this->type === 'service';
    }

    public function isProduct(): bool
    {
        return $this->type === 'product';
    }

    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }
        return $this->stock_quantity > 0;
    }

    public function isLowStock(): bool
    {
        if (!$this->track_inventory || !$this->stock_alert_threshold) {
            return false;
        }
        return $this->stock_quantity <= $this->stock_alert_threshold;
    }

    public function hasDiscount(): bool
    {
        return $this->compare_at_price && $this->compare_at_price > $this->price;
    }

    public function getDiscountPercentage(): float
    {
        if (!$this->hasDiscount()) {
            return 0;
        }
        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 1);
    }

    public function getMargin(): ?float
    {
        if (!$this->cost_price || $this->cost_price == 0) {
            return null;
        }
        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    public function incrementSales(int $quantity = 1): void
    {
        $this->increment('sales_count', $quantity);
    }

    public function decrementStock(float $quantity): void
    {
        if ($this->track_inventory) {
            $this->decrement('stock_quantity', $quantity);
        }
    }

    public function incrementStock(float $quantity): void
    {
        if ($this->track_inventory) {
            $this->increment('stock_quantity', $quantity);
        }
    }
}
