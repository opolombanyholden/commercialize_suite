<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, Loggable;

    protected $fillable = [
        'company_id',
        'site_id',
        'user_id',
        'client_id',
        'quote_number',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'client_city',
        'client_postal_code',
        'quote_date',
        'valid_until',
        'subtotal',
        'tax_amount',
        'total_amount',
        'total_in_words',
        'notes',
        'terms',
        'status',
        'converted_to_invoice_id',
        'converted_at',
        'pdf_path',
        'pdf_generated_at',
        'sent_at',
        'viewed_at',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'converted_at' => 'datetime',
        'pdf_generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (empty($quote->quote_number)) {
                $quote->quote_number = static::generateNumber($quote->company_id);
            }
        });
    }

    /**
     * Generate unique quote number
     */
    public static function generateNumber(int $companyId): string
    {
        $prefix = 'Q';
        $date = now()->format('Ymd');
        
        $lastQuote = static::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastQuote && preg_match('/(\d+)$/', $lastQuote->quote_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $date, $sequence);
    }

    // ===== RELATIONS =====

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(QuoteTax::class);
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_to_invoice_id');
    }

    // ===== SCOPES =====

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'sent']);
    }

    public function scopeNotConverted($query)
    {
        return $query->whereNull('converted_to_invoice_id');
    }

    // ===== HELPERS =====

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->valid_until && $this->valid_until->isPast();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->isExpired();
    }

    public function isConverted(): bool
    {
        return $this->converted_to_invoice_id !== null;
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeDeleted(): bool
    {
        return $this->isDraft() && !$this->isConverted();
    }

    public function canBeConverted(): bool
    {
        return in_array($this->status, ['draft', 'sent', 'accepted']) && !$this->isConverted();
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsViewed(): void
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function markAsAccepted(): void
    {
        $this->update(['status' => 'accepted']);
    }

    public function markAsDeclined(): void
    {
        $this->update(['status' => 'declined']);
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $taxAmount = $this->taxes()->sum('tax_amount');

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
        ]);
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'accepted' => 'success',
            'declined' => 'danger',
            'expired' => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyé',
            'accepted' => 'Accepté',
            'declined' => 'Refusé',
            'expired' => 'Expiré',
            default => $this->status,
        };
    }
}
