<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'tax_id',
        'tax_name',
        'tax_rate',
        'apply_to',
        'taxable_base',
        'tax_amount',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'taxable_base' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($quoteTax) {
            // Recalculer les totaux du devis
            $quoteTax->quote->calculateTotals();
        });

        static::deleted(function ($quoteTax) {
            // Recalculer les totaux du devis
            $quoteTax->quote->calculateTotals();
        });
    }

    // ===== RELATIONS =====

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    // ===== HELPERS =====

    /**
     * Obtenir le taux formaté (ex: "18%")
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->tax_rate, $this->tax_rate == floor($this->tax_rate) ? 0 : 2) . '%';
    }

    /**
     * Recalculer le montant de taxe basé sur les items du devis
     */
    public function recalculate(): void
    {
        $quote = $this->quote;
        $items = $quote->items;

        // Calculer la base imposable selon le type d'application
        $taxableBase = 0;

        foreach ($items as $item) {
            if ($this->apply_to === 'all') {
                $taxableBase += $item->total;
            } elseif ($this->apply_to === 'products' && $item->type === 'product') {
                $taxableBase += $item->total;
            } elseif ($this->apply_to === 'services' && $item->type === 'service') {
                $taxableBase += $item->total;
            }
        }

        $this->taxable_base = $taxableBase;
        $this->tax_amount = round($taxableBase * ($this->tax_rate / 100), 2);
        $this->saveQuietly();
    }

    /**
     * Créer à partir d'un objet Tax
     */
    public static function fromTax(Tax $tax, float $taxableBase): array
    {
        return [
            'tax_id' => $tax->id,
            'tax_name' => $tax->name,
            'tax_rate' => $tax->rate,
            'apply_to' => $tax->apply_to,
            'taxable_base' => $taxableBase,
            'tax_amount' => round($taxableBase * ($tax->rate / 100), 2),
        ];
    }
}
