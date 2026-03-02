<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'type',
        'name',
        'company_name',
        'tax_id',
        'email',
        'phone',
        'mobile',
        'website',
        'address',
        'city',
        'postal_code',
        'state',
        'country',
        'payment_terms',
        'credit_limit',
        'notes',
        'tags',
        'total_spent',
        'orders_count',
        'last_order_at',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'credit_limit' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'orders_count' => 'integer',
        'last_order_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONS =====

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIndividuals($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeBusinesses($query)
    {
        return $query->where('type', 'business');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    public function scopeTopSpenders($query, int $limit = 10)
    {
        return $query->orderBy('total_spent', 'desc')->limit($limit);
    }

    // ===== HELPERS =====

    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'business' && $this->company_name) {
            return $this->company_name;
        }
        return $this->name;
    }

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

    public function isBusiness(): bool
    {
        return $this->type === 'business';
    }

    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function hasReachedCreditLimit(): bool
    {
        if (!$this->credit_limit) {
            return false;
        }

        $unpaidAmount = $this->invoices()
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('balance');

        return $unpaidAmount >= $this->credit_limit;
    }

    public function updateStats(): void
    {
        $this->update([
            'total_spent' => $this->invoices()->where('payment_status', 'paid')->sum('total_amount'),
            'orders_count' => $this->invoices()->count(),
            'last_order_at' => $this->invoices()->latest()->first()?->created_at,
        ]);
    }

    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    public function removeTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        $tags = array_filter($tags, fn($t) => $t !== $tag);
        $this->update(['tags' => array_values($tags)]);
    }
}
