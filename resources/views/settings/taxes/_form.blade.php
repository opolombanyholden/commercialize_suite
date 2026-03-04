<div class="mb-3">
    <label for="name" class="form-label">Nom de la taxe <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror"
           id="name" name="name" value="{{ old('name', $tax->name ?? '') }}"
           required maxlength="50" placeholder="Ex: TPS Standard">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="rate" class="form-label">Taux (%) <span class="text-danger">*</span></label>
    <div class="input-group">
        <input type="number" class="form-control @error('rate') is-invalid @enderror"
               id="rate" name="rate" value="{{ old('rate', $tax->rate ?? '') }}"
               required min="-100" max="100" step="0.01" placeholder="18">
        <span class="input-group-text">%</span>
    </div>
    @error('rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="apply_to" class="form-label">S'applique à</label>
    <select class="form-select @error('apply_to') is-invalid @enderror" id="apply_to" name="apply_to">
        <option value="all"      {{ old('apply_to', $tax->apply_to ?? 'all') === 'all'      ? 'selected' : '' }}>Tous (produits et services)</option>
        <option value="products" {{ old('apply_to', $tax->apply_to ?? '')    === 'products' ? 'selected' : '' }}>Produits uniquement</option>
        <option value="services" {{ old('apply_to', $tax->apply_to ?? '')    === 'services' ? 'selected' : '' }}>Services uniquement</option>
    </select>
    @error('apply_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-3">
    <label for="description" class="form-label">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror"
              id="description" name="description" rows="2" maxlength="255">{{ old('description', $tax->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                   {{ old('is_active', $tax->is_active ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_active">Taxe active</label>
        </div>
    </div>
    <div class="col-6">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1"
                   {{ old('is_default', $tax->is_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_default">Taxe par défaut</label>
        </div>
    </div>
</div>
