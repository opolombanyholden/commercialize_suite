<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Traits\BelongsToCompany;
use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, SoftDeletes, BelongsToCompany, Loggable;

    protected $fillable = [
        'company_id',
        'invoice_id',
        'user_id',
        'site_id',
        'payment_number',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
        'is_confirmed',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'payment_method' => PaymentMethod::class,
        'is_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = static::generateNumber($payment->company_id);
            }
        });

        static::created(function ($payment) {
            // Mettre à jour le montant payé de la facture
            $payment->invoice->recordPayment($payment->amount);
        });

        static::deleted(function ($payment) {
            // Recalculer le montant payé de la facture
            $invoice = $payment->invoice;
            $totalPaid = $invoice->payments()->where('id', '!=', $payment->id)->sum('amount');
            
            $balance = $invoice->total_amount - $totalPaid;
            $paymentStatus = $totalPaid <= 0 ? 'unpaid' : ($balance <= 0 ? 'paid' : 'partial');

            $invoice->update([
                'paid_amount' => $totalPaid,
                'balance' => max(0, $balance),
                'payment_status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
            ]);
        });
    }

    public static function generateNumber(int $companyId): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');
        
        $lastPayment = static::where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = 1;
        if ($lastPayment && preg_match('/(\d+)$/', $lastPayment->payment_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $date, $sequence);
    }

    // ===== RELATIONS =====

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // ===== SCOPES =====

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_confirmed', false);
    }

    public function scopeByMethod($query, PaymentMethod $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // ===== HELPERS =====

    public function confirm(?int $userId = null): void
    {
        $this->update([
            'is_confirmed' => true,
            'confirmed_at' => now(),
            'confirmed_by' => $userId ?? auth()->id(),
        ]);
    }

    public function getMethodLabelAttribute(): string
    {
        return $this->payment_method->label();
    }

    public function getMethodIconAttribute(): string
    {
        return match($this->payment_method) {
            PaymentMethod::CASH => 'fa-money-bill-wave',
            PaymentMethod::CHECK => 'fa-money-check',
            PaymentMethod::BANK_TRANSFER => 'fa-building-columns',
            PaymentMethod::CREDIT_CARD => 'fa-credit-card',
            PaymentMethod::MOBILE_MONEY => 'fa-mobile-screen',
            default => 'fa-circle-dollar-to-slot',
        };
    }
}
