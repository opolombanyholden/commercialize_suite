{{-- Formulaire partagé création / édition promotion --}}
<div class="row g-4">

    {{-- Infos principales --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-tag me-2 text-primary"></i>Informations</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="code" class="form-label fw-semibold">Code promo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase @error('code') is-invalid @enderror"
                               id="code" name="code"
                               value="{{ old('code', $promotion->code ?? '') }}"
                               placeholder="Ex: SOLDES20" maxlength="50" required
                               oninput="this.value = this.value.toUpperCase()">
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Unique, sans espaces.</div>
                    </div>
                    <div class="col-md-8">
                        <label for="name" class="form-label fw-semibold">Nom de la promotion <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name"
                               value="{{ old('name', $promotion->name ?? '') }}"
                               placeholder="Ex: Soldes d'été 2026" maxlength="255" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description <span class="text-muted">(optionnel)</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="2" maxlength="500"
                                  placeholder="Conditions d'utilisation, détails...">{{ old('description', $promotion->description ?? '') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Remise --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-percent me-2 text-success"></i>Remise</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="discount_type" class="form-label fw-semibold">Type de remise <span class="text-danger">*</span></label>
                        <select class="form-select @error('discount_type') is-invalid @enderror"
                                id="discount_type" name="discount_type" required>
                            <option value="">Choisir...</option>
                            <option value="percent" {{ old('discount_type', $promotion->discount_type ?? '') === 'percent' ? 'selected' : '' }}>
                                Pourcentage (%)
                            </option>
                            <option value="amount" {{ old('discount_type', $promotion->discount_type ?? '') === 'amount' ? 'selected' : '' }}>
                                Montant fixe (FCFA)
                            </option>
                        </select>
                        @error('discount_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="discount_value" class="form-label fw-semibold">Valeur <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('discount_value') is-invalid @enderror"
                                   id="discount_value" name="discount_value"
                                   value="{{ old('discount_value', $promotion->discount_value ?? '') }}"
                                   min="0.01" step="0.01" required>
                            <span class="input-group-text" id="discountSuffix">%</span>
                        </div>
                        @error('discount_value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label for="applies_to" class="form-label fw-semibold">Appliquée sur</label>
                        <select class="form-select @error('applies_to') is-invalid @enderror"
                                id="applies_to" name="applies_to">
                            <option value="global" {{ old('applies_to', $promotion->applies_to ?? 'global') === 'global' ? 'selected' : '' }}>Total HT</option>
                            <option value="products" {{ old('applies_to', $promotion->applies_to ?? '') === 'products' ? 'selected' : '' }}>Produits seulement</option>
                            <option value="services" {{ old('applies_to', $promotion->applies_to ?? '') === 'services' ? 'selected' : '' }}>Services seulement</option>
                        </select>
                        @error('applies_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="min_amount" class="form-label">Montant minimum <span class="text-muted">(optionnel)</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('min_amount') is-invalid @enderror"
                                   id="min_amount" name="min_amount"
                                   value="{{ old('min_amount', $promotion->min_amount ?? '') }}"
                                   min="0" step="100" placeholder="0">
                            <span class="input-group-text">FCFA</span>
                        </div>
                        @error('min_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">La commande doit atteindre ce montant pour bénéficier de la remise.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Limites et validité --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-calendar-alt me-2 text-warning"></i>Validité</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="valid_from" class="form-label">Début de validité</label>
                    <input type="date" class="form-control @error('valid_from') is-invalid @enderror"
                           id="valid_from" name="valid_from"
                           value="{{ old('valid_from', isset($promotion->valid_from) ? $promotion->valid_from->format('Y-m-d') : '') }}">
                    @error('valid_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="valid_until" class="form-label">Fin de validité</label>
                    <input type="date" class="form-control @error('valid_until') is-invalid @enderror"
                           id="valid_until" name="valid_until"
                           value="{{ old('valid_until', isset($promotion->valid_until) ? $promotion->valid_until->format('Y-m-d') : '') }}">
                    @error('valid_until')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="max_uses" class="form-label">Nombre max d'utilisations</label>
                    <input type="number" class="form-control @error('max_uses') is-invalid @enderror"
                           id="max_uses" name="max_uses"
                           value="{{ old('max_uses', $promotion->max_uses ?? '') }}"
                           min="1" step="1" placeholder="Illimité">
                    @error('max_uses')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Laisser vide pour un usage illimité.</div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                           value="1" {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="is_active">Promotion active</label>
                </div>
                <div class="form-text mt-1">Une promotion inactive ne peut pas être appliquée.</div>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="col-12">
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Enregistrer
            </button>
            <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('discount_type').addEventListener('change', function () {
    document.getElementById('discountSuffix').textContent = this.value === 'percent' ? '%' : 'FCFA';
});
// Init
(function() {
    const t = document.getElementById('discount_type').value;
    document.getElementById('discountSuffix').textContent = t === 'amount' ? 'FCFA' : '%';
})();
</script>
@endpush
