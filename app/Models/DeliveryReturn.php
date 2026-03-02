<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryReturn extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, Loggable;

    protected $fillable = [
        'company_id',
        'site_id',
        'user_id',
        'invoice_id',
        'delivery_note_id',
        'client_id',
        'return_number',
        'client_name',
        'status',
        'reason',
        'notes',
        'resolution',
        'new_delivery_id',
        'credit_note_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $r) {
            if (empty($r->return_number)) {
                $r->return_number = static::generateNumber();
            }
        });
    }

    public static function generateNumber(): string
    {
        return sprintf('RET-%s-%d', now()->format('Ymd'), now()->timestamp);
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

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'delivery_note_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryReturnItem::class)->orderBy('sort_order');
    }

    public function newDelivery(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'new_delivery_id');
    }

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'credit_note_id');
    }

    // ===== HELPERS =====

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isReceived(): bool  { return $this->status === 'received'; }
    public function isResolved(): bool  { return $this->status === 'resolved'; }
    public function canBeResolved(): bool { return $this->status === 'received'; }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'warning',
            'received' => 'info',
            'resolved' => 'success',
            default    => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending'  => 'En attente',
            'received' => 'Reçu',
            'resolved' => 'Résolu',
            default    => $this->status,
        };
    }

    public function getResolutionLabelAttribute(): ?string
    {
        return match($this->resolution) {
            're_delivery' => 'Nouvelle livraison',
            'credit_note' => 'Avoir émis',
            default       => null,
        };
    }

    /**
     * Quantités retournées (status received ou resolved) par clé d'article pour une facture donnée.
     */
    public static function returnedQtiesForInvoice(int $invoiceId): array
    {
        $qtys = [];
        $returns = static::where('invoice_id', $invoiceId)
            ->whereIn('status', ['received', 'resolved'])
            ->with('items')
            ->get();

        foreach ($returns as $ret) {
            foreach ($ret->items as $item) {
                $key = DeliveryNote::itemKey($item->product_id, $item->description);
                $qtys[$key] = ($qtys[$key] ?? 0) + (float) $item->quantity_returned;
            }
        }

        return $qtys;
    }
}
