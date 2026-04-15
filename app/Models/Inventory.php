<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'site_id',
        'user_id',
        'name',
        'date',
        'status',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'date'         => 'date',
        'completed_at' => 'datetime',
    ];

    // ===== RELATIONS =====

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InventoryLine::class)->with('product');
    }

    // ===== SCOPES =====

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // ===== HELPERS =====

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'       => 'Brouillon',
            'in_progress' => 'En cours',
            'completed'   => 'Terminé',
            default       => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'draft'       => 'secondary',
            'in_progress' => 'warning',
            'completed'   => 'success',
            default       => 'secondary',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function getTotalGoodAttribute(): int
    {
        return $this->lines->sum('good_quantity') ?? 0;
    }

    public function getTotalDamagedAttribute(): int
    {
        return $this->lines->sum('damaged_quantity') ?? 0;
    }

    public function getDiscrepancyAttribute(): int
    {
        return $this->lines->sum(function ($line) {
            $counted = ($line->good_quantity ?? 0) + ($line->damaged_quantity ?? 0);
            return $counted - $line->expected_quantity;
        });
    }
}
