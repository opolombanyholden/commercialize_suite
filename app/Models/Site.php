<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'description',
        'is_headquarters',
        'is_warehouse',
        'is_store',
        'email',
        'phone',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
        'latitude',
        'longitude',
        'manager_id',
        'settings',
        'business_hours',
        'is_active',
    ];

    protected $casts = [
        'is_headquarters' => 'boolean',
        'is_warehouse' => 'boolean',
        'is_store' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
        'business_hours' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    // ===== RELATIONS =====

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_site_access')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class)->latest();
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHeadquarters($query)
    {
        return $query->where('is_headquarters', true);
    }

    public function scopeWarehouses($query)
    {
        return $query->where('is_warehouse', true);
    }

    public function scopeStores($query)
    {
        return $query->where('is_store', true);
    }

    // ===== HELPERS =====

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->postal_code,
            $this->state,
            $this->country,
        ]);

        return implode(', ', $parts);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }
}
