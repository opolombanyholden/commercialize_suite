<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Traits\BelongsToCompany;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, Loggable;

    protected $fillable = [
        'company_id',
        'site_id',
        'user_id',
        'client_id',
        'quote_id',
        'type',
        'original_invoice_id',
        'invoice_number',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'client_city',
        'client_postal_code',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'total_in_words',
        'notes',
        'terms',
        'status',
        'payment_status',
        'pdf_path',
        'pdf_generated_at',
        'sent_at',
        'viewed_at',
        'paid_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'pdf_generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateNumber($invoice->company_id, $invoice->type ?? 'invoice');
            }
            // Calculer le solde initial
            $invoice->balance = $invoice->total_amount - ($invoice->paid_amount ?? 0);
        });
    }

    /**
     * Generate unique invoice number
     */
    public static function generateNumber(int $companyId, string $type = 'invoice'): string
    {
        $prefix = $type === 'credit_note' ? 'AV' : 'F';
        return sprintf('%s-%s-%d', $prefix, now()->format('Ymd'), now()->timestamp);
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

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(InvoiceTax::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class)->orderBy('created_at');
    }

    public function deliveryReturns(): HasMany
    {
        return $this->hasMany(DeliveryReturn::class)->orderBy('created_at');
    }

    public function originalInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'original_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'original_invoice_id')->where('type', 'credit_note');
    }

    /**
     * Retourne le résumé de l'avancement des livraisons pour cet invoice.
     * [total_dn, delivered, in_transit, pending, fully_delivered]
     */
    public function getDeliveryProgressAttribute(): array
    {
        $notes = $this->deliveryNotes ?? $this->deliveryNotes()->get();

        $active = $notes->where('status', '!=', 'cancelled');
        $total        = $active->count();
        $delivered    = $active->where('status', 'delivered')->count();
        $inTransit    = $active->where('status', 'in_transit')->count();
        $pending      = $active->where('status', 'pending')->count();

        return [
            'total'          => $total,
            'delivered'      => $delivered,
            'in_transit'     => $inTransit,
            'pending'        => $pending,
            'fully_delivered'=> $total > 0 && $delivered === $total,
        ];
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

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', 'partial');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }

    public function scopePending($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }

    public function scopeInvoicesOnly($query)
    {
        return $query->where('type', 'invoice');
    }

    public function scopeCreditNotes($query)
    {
        return $query->where('type', 'credit_note');
    }

    // ===== HELPERS =====

    public function isCreditNote(): bool
    {
        return $this->type === 'credit_note';
    }

    /**
     * Vérifie si toutes les quantités commandées ont été livrées (net des retours).
     */
    public function isFullyDelivered(): bool
    {
        $this->loadMissing('items');
        if ($this->items->isEmpty()) {
            return false;
        }

        $netDelivered = DeliveryNote::netDeliveredQtiesForInvoice($this->id);

        foreach ($this->items as $item) {
            $key = DeliveryNote::itemKey($item->product_id, $item->description);
            $remaining = max(0, (float) $item->quantity - ($netDelivered[$key] ?? 0));
            if ($remaining > 0.001) {
                return false;
            }
        }

        return true;
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === 'partial';
    }

    public function isUnpaid(): bool
    {
        return $this->payment_status === 'unpaid';
    }

    public function isOverdue(): bool
    {
        if ($this->isPaid()) {
            return false;
        }
        return $this->due_date && $this->due_date->isPast();
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->isOverdue();
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft() && $this->paid_amount == 0;
    }

    public function canBeDeleted(): bool
    {
        return $this->isDraft() && $this->paid_amount == 0;
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
            $this->update([
                'status' => 'viewed',
                'viewed_at' => now(),
            ]);
        }
    }

    public function recordPayment(float $amount): void
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newBalance = $this->total_amount - $newPaidAmount;

        $paymentStatus = 'unpaid';
        if ($newBalance <= 0) {
            $paymentStatus = 'paid';
            $newBalance = 0;
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        }

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance' => $newBalance,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus === 'paid' ? 'paid' : $this->status,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        // Mettre à jour les statistiques du client
        if ($this->client) {
            $this->client->updateStats();
        }
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items()->sum('total');
        $taxAmount = $this->taxes()->sum('tax_amount');
        $totalAmount = $subtotal + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance' => $totalAmount - $this->paid_amount,
        ]);
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->pdf_path ? asset('storage/' . $this->pdf_path) : null;
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->isPaid()) {
            return 'success';
        }
        if ($this->isOverdue()) {
            return 'danger';
        }
        if ($this->isPartiallyPaid()) {
            return 'warning';
        }

        return match($this->status) {
            'draft' => 'secondary',
            'sent' => 'info',
            'viewed' => 'primary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isPaid()) {
            return 'Payée';
        }
        if ($this->isOverdue()) {
            return 'En retard';
        }
        if ($this->isPartiallyPaid()) {
            return 'Paiement partiel';
        }

        return match($this->status) {
            'draft' => 'Brouillon',
            'sent' => 'Envoyée',
            'viewed' => 'Vue',
            'cancelled' => 'Annulée',
            default => $this->status,
        };
    }

    /**
     * Créer une facture à partir d'un devis
     */
    public static function createFromQuote(Quote $quote): self
    {
        $invoice = static::create([
            'company_id' => $quote->company_id,
            'site_id' => $quote->site_id,
            'user_id' => auth()->id() ?? $quote->user_id,
            'client_id' => $quote->client_id,
            'quote_id' => $quote->id,
            'client_name' => $quote->client_name,
            'client_email' => $quote->client_email,
            'client_phone' => $quote->client_phone,
            'client_address' => $quote->client_address,
            'client_city' => $quote->client_city,
            'client_postal_code' => $quote->client_postal_code,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $quote->subtotal,
            'tax_amount' => $quote->tax_amount,
            'total_amount' => $quote->total_amount,
            'total_in_words' => $quote->total_in_words,
            'notes' => $quote->notes,
            'terms' => $quote->terms,
            'status' => 'draft',
            'payment_status' => 'unpaid',
        ]);

        // Copier les items
        foreach ($quote->items as $item) {
            $invoice->items()->create([
                'product_id' => $item->product_id,
                'description' => $item->description,
                'details' => $item->details,
                'type' => $item->type,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'sort_order' => $item->sort_order,
            ]);
        }

        // Copier les taxes
        foreach ($quote->taxes as $tax) {
            $invoice->taxes()->create([
                'tax_id' => $tax->tax_id,
                'tax_name' => $tax->tax_name,
                'tax_rate' => $tax->tax_rate,
                'apply_to' => $tax->apply_to,
                'taxable_base' => $tax->taxable_base,
                'tax_amount' => $tax->tax_amount,
            ]);
        }

        // Marquer le devis comme converti
        $quote->update([
            'converted_to_invoice_id' => $invoice->id,
            'converted_at' => now(),
        ]);

        return $invoice;
    }
}
