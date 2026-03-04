<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DeliveryNote extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, Loggable;

    protected $fillable = [
        'company_id',
        'site_id',
        'user_id',
        'invoice_id',
        'client_id',
        'delivery_number',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'delivery_address',
        'planned_date',
        'delivered_date',
        'status',
        'livreur',
        'notes',
        'signature',
        'pdf_path',
        'pdf_generated_at',
        'public_token',
        'pin_verified',
        'pin_verified_at',
        'pin_verified_by',
    ];

    protected $casts = [
        'planned_date'     => 'date',
        'delivered_date'   => 'date',
        'pdf_generated_at' => 'datetime',
        'pin_verified'     => 'boolean',
        'pin_verified_at'  => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $dn) {
            if (empty($dn->delivery_number)) {
                $dn->delivery_number = static::generateNumber($dn->company_id);
            }
            if (empty($dn->public_token)) {
                $dn->public_token = (string) Str::uuid();
            }
        });
    }

    public static function generateNumber(int $companyId): string
    {
        return sprintf('BL-%s-%d', now()->format('Ymd'), now()->timestamp);
    }

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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class)->orderBy('sort_order');
    }

    // ===== SCOPES =====

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // ===== HELPERS =====

    public function isPending(): bool    { return $this->status === 'pending'; }
    public function isInTransit(): bool  { return $this->status === 'in_transit'; }
    public function isDelivered(): bool  { return $this->status === 'delivered'; }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }
    public function isPinVerified(): bool { return (bool) $this->pin_verified; }

    public function getPublicUrlAttribute(): string
    {
        return route('delivery.public', $this->public_token);
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['pending', 'in_transit']);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'warning',
            'in_transit' => 'info',
            'delivered'  => 'success',
            'cancelled'  => 'secondary',
            default      => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'    => 'En attente',
            'in_transit' => 'En transit',
            'delivered'  => 'Livré',
            'cancelled'  => 'Annulé',
            default      => $this->status,
        };
    }

    /**
     * Clé de correspondance pour relier un item de livraison à un item de facture
     */
    public static function itemKey(int|null $productId, string $description): string
    {
        return $productId ? "p_{$productId}" : 'd_' . md5(trim($description));
    }

    /**
     * Quantités nettes livrées = livrées brutes − retournées (received/resolved).
     * À utiliser pour calculer les quantités restantes à livrer.
     */
    public static function netDeliveredQtiesForInvoice(int $invoiceId, int|null $excludeId = null): array
    {
        $delivered = static::deliveredQtiesForInvoice($invoiceId, $excludeId);
        $returned  = \App\Models\DeliveryReturn::returnedQtiesForInvoice($invoiceId);

        foreach ($returned as $key => $qty) {
            $delivered[$key] = max(0, ($delivered[$key] ?? 0) - $qty);
        }

        return $delivered;
    }

    /**
     * Quantités déjà livrées (BL au statut "delivered") brutes, par clé d'article.
     * En excluant éventuellement un BL précis (pour le récap de ce BL lui-même).
     */
    public static function deliveredQtiesForInvoice(int $invoiceId, int|null $excludeId = null): array
    {
        $query = static::where('invoice_id', $invoiceId)
            ->where('status', 'delivered')
            ->with('items');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $qtys = [];
        foreach ($query->get() as $dn) {
            foreach ($dn->items as $item) {
                $key = static::itemKey($item->product_id, $item->description);
                $qtys[$key] = ($qtys[$key] ?? 0) + (float) $item->quantity;
            }
        }
        return $qtys;
    }

    /**
     * Récapitulatif de cette livraison par rapport à la facture source.
     * Retourne un tableau indexé par clé d'article :
     * [description, unit, ordered, already_delivered, this_delivery, remaining]
     */
    public function getDeliveryRecap(): array
    {
        if (!$this->invoice_id) {
            return [];
        }

        if ($this->relationLoaded('invoice') && $this->invoice) {
            $invoice = $this->invoice;
            $invoice->loadMissing('items');
        } else {
            $invoice = Invoice::with('items')->find($this->invoice_id);
        }
        if (!$invoice) {
            return [];
        }

        // Quantités nettes déjà livrées par les AUTRES BL livrés (retours soustraits)
        $alreadyDelivered = static::netDeliveredQtiesForInvoice($this->invoice_id, $this->id);

        // Quantités commandées indexées par clé
        $orderedQtys = [];
        foreach ($invoice->items as $invItem) {
            $key = static::itemKey($invItem->product_id, $invItem->description);
            $orderedQtys[$key] = (float) $invItem->quantity;
        }

        $recap = [];
        foreach ($this->items as $dnItem) {
            $key         = static::itemKey($dnItem->product_id, $dnItem->description);
            $ordered     = $orderedQtys[$key] ?? null;
            $prevDel     = $alreadyDelivered[$key] ?? 0;
            $thisDel     = (float) $dnItem->quantity;
            $remaining   = $ordered !== null ? max(0, $ordered - $prevDel - $thisDel) : null;

            $recap[] = [
                'description'       => $dnItem->description,
                'unit'              => $dnItem->unit,
                'ordered'           => $ordered,
                'already_delivered' => $prevDel,
                'this_delivery'     => $thisDel,
                'remaining'         => $remaining,
            ];
        }

        return $recap;
    }

    /**
     * Créer un bon de livraison depuis une facture.
     * Pré-remplit uniquement les quantités RESTANTES à livrer.
     */
    public static function createFromInvoice(Invoice $invoice): self
    {
        $invoice->loadMissing('items');

        $dn = static::create([
            'company_id'       => $invoice->company_id,
            'site_id'          => $invoice->site_id,
            'user_id'          => auth()->id() ?? $invoice->user_id,
            'invoice_id'       => $invoice->id,
            'client_id'        => $invoice->client_id,
            'client_name'      => $invoice->client_name,
            'client_email'     => $invoice->client_email,
            'client_phone'     => $invoice->client_phone,
            'client_address'   => $invoice->client_address,
            'delivery_address' => $invoice->client_address,
            'planned_date'     => now()->addDay(),
            'status'           => 'pending',
        ]);

        // Calcul des quantités déjà livrées
        $alreadyDelivered = static::deliveredQtiesForInvoice($invoice->id);

        foreach ($invoice->items as $item) {
            $key       = static::itemKey($item->product_id, $item->description);
            $delivered = $alreadyDelivered[$key] ?? 0;
            $remaining = max(0, (float) $item->quantity - $delivered);

            if ($remaining > 0) {
                $dn->items()->create([
                    'product_id'  => $item->product_id,
                    'description' => $item->description,
                    'quantity'    => $remaining,
                    'sort_order'  => $item->sort_order,
                ]);
            }
        }

        return $dn;
    }
}
