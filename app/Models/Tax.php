<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'rate',
        'description',
        'apply_to',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ===== RELATIONS =====

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForProducts($query)
    {
        return $query->whereIn('apply_to', ['all', 'products']);
    }

    public function scopeForServices($query)
    {
        return $query->whereIn('apply_to', ['all', 'services']);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ===== HELPERS =====

    /**
     * Calculer le montant de taxe pour un montant donné
     */
    public function calculate(float $amount): float
    {
        return round($amount * ($this->rate / 100), 2);
    }

    /**
     * Obtenir le taux formaté (ex: "18%")
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, $this->rate == floor($this->rate) ? 0 : 2) . '%';
    }

    /**
     * Peut s'appliquer à un type d'item
     */
    public function appliesTo(string $type): bool
    {
        if ($this->apply_to === 'all') {
            return true;
        }

        return $this->apply_to === $type || $this->apply_to === $type . 's';
    }

    /**
     * Définir comme taxe par défaut (et retirer le statut des autres)
     */
    public function makeDefault(): void
    {
        // Retirer le statut default des autres taxes de la même company
        static::where('company_id', $this->company_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
