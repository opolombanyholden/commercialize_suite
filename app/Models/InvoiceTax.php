<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
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

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($invoiceTax) {
            $invoiceTax->invoice->calculateTotals();
        });

        static::deleted(function ($invoiceTax) {
            $invoiceTax->invoice->calculateTotals();
        });
    }

    // ===== RELATIONS =====

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    // ===== HELPERS =====

    public function getFormattedRateAttribute(): string
    {
        return number_format($this->tax_rate, $this->tax_rate == floor($this->tax_rate) ? 0 : 2) . '%';
    }

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
