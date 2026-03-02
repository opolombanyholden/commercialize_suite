<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image_path',
        'alt_text',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    // ===== RELATIONS =====

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ===== SCOPES =====

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    // ===== HELPERS =====

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }

    public function makePrimary(): void
    {
        // Retirer le statut primary des autres images
        $this->product->images()->where('id', '!=', $this->id)->update(['is_primary' => false]);
        
        // Définir cette image comme primary
        $this->update(['is_primary' => true]);
        
        // Mettre à jour l'image principale du produit
        $this->product->update(['main_image_path' => $this->image_path]);
    }
}
