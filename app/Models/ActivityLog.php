<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ===== RELATIONS =====

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ===== SCOPES =====

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ===== HELPERS =====

    public static function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $user = auth()->user();

        return static::create([
            'company_id' => $user?->company_id,
            'user_id' => $user?->id,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'action' => $action,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Créé',
            'updated' => 'Modifié',
            'deleted' => 'Supprimé',
            'restored' => 'Restauré',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'exported' => 'Exporté',
            'imported' => 'Importé',
            'sent' => 'Envoyé',
            'viewed' => 'Consulté',
            'converted' => 'Converti',
            'paid' => 'Payé',
            default => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            'login' => 'primary',
            'logout' => 'secondary',
            default => 'secondary',
        };
    }

    public function getSubjectNameAttribute(): string
    {
        if (!$this->subject) {
            return 'Élément supprimé';
        }

        // Essayer différents attributs pour obtenir un nom
        return $this->subject->name 
            ?? $this->subject->title 
            ?? $this->subject->invoice_number 
            ?? $this->subject->quote_number 
            ?? "#{$this->subject_id}";
    }

    public function getChanges(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
