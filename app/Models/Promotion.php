<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'applies_to',
        'min_amount',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_amount'     => 'decimal:2',
        'valid_from'     => 'date',
        'valid_until'    => 'date',
        'is_active'      => 'boolean',
        'max_uses'       => 'integer',
        'uses_count'     => 'integer',
    ];

    // ===== RELATIONS =====

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    // ===== SCOPES =====

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByCode(Builder $query, string $code, int $companyId): Builder
    {
        return $query->where('company_id', $companyId)
                     ->whereRaw('UPPER(code) = ?', [strtoupper($code)]);
    }

    // ===== HELPERS =====

    /**
     * Vérifie si la promotion est valide pour un montant de commande donné.
     */
    public function isValid(float $orderAmount = 0): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->toDateString() < $today) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        if ($this->min_amount !== null && $orderAmount < (float) $this->min_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calcule la remise pour un montant de base donné.
     */
    public function calculateDiscount(float $base): float
    {
        if ($this->discount_type === 'percent') {
            return round($base * (float) $this->discount_value / 100, 2);
        }

        return min((float) $this->discount_value, $base);
    }

    /**
     * Raison de l'invalidité pour le message d'erreur.
     */
    public function getInvalidReason(float $orderAmount = 0): string
    {
        if (!$this->is_active) {
            return 'Cette promotion est désactivée.';
        }

        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return 'Cette promotion n\'est pas encore active (début le ' . $this->valid_from->format('d/m/Y') . ').';
        }

        if ($this->valid_until && $this->valid_until->toDateString() < $today) {
            return 'Cette promotion a expiré le ' . $this->valid_until->format('d/m/Y') . '.';
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return 'Cette promotion a atteint son nombre d\'utilisations maximum.';
        }

        if ($this->min_amount !== null && $orderAmount < (float) $this->min_amount) {
            return 'Montant minimum requis : ' . number_format((float) $this->min_amount, 0, ',', ' ') . ' FCFA.';
        }

        return 'Code promotionnel invalide.';
    }

    public function getDiscountLabelAttribute(): string
    {
        return $this->discount_type === 'percent'
            ? $this->discount_value . '%'
            : number_format((float) $this->discount_value, 0, ',', ' ') . ' FCFA';
    }
}
