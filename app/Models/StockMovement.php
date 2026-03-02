<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'site_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'reference',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'stock_before' => 'integer',
        'stock_after'  => 'integer',
        'unit_cost'    => 'decimal:2',
    ];

    // ===== RELATIONS =====

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ===== SCOPES =====

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeIns($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeOuts($query)
    {
        return $query->where('quantity', '<', 0);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ===== HELPERS =====

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'in'          => 'Entrée de stock',
            'out'         => 'Sortie manuelle',
            'sale'        => 'Vente',
            'return'      => 'Retour client',
            'adjustment'  => 'Ajustement',
            'inventory'   => 'Inventaire',
            'loss'        => 'Perte / Casse',
            default       => ucfirst($type),
        };
    }

    public static function typeBadgeClass(string $type): string
    {
        return match ($type) {
            'in'         => 'success',
            'out', 'loss' => 'danger',
            'sale'       => 'warning',
            'return'     => 'info',
            'adjustment' => 'secondary',
            'inventory'  => 'primary',
            default      => 'secondary',
        };
    }

    public static function typeIcon(string $type): string
    {
        return match ($type) {
            'in'         => 'fa-arrow-down',
            'out'        => 'fa-arrow-up',
            'sale'       => 'fa-shopping-cart',
            'return'     => 'fa-undo',
            'adjustment' => 'fa-sliders-h',
            'inventory'  => 'fa-clipboard-list',
            'loss'       => 'fa-exclamation-triangle',
            default      => 'fa-exchange-alt',
        };
    }

    public function isPositive(): bool
    {
        return $this->quantity > 0;
    }

    public function getAbsoluteQuantityAttribute(): int
    {
        return abs($this->quantity);
    }
}
