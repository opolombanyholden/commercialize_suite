@extends('layouts.admin')
@section('title', 'Nouveau mouvement de stock')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.movements.index') }}">Mouvements</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('page-header')
<h1 class="page-title mb-0">Nouveau mouvement de stock</h1>
@endsection

@section('content')
<form action="{{ route('inventory.movements.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i>Détails du mouvement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Produit --}}
                        <div class="col-12">
                            <label for="product_id" class="form-label">Produit <span class="text-danger">*</span></label>
                            <select class="form-select @error('product_id') is-invalid @enderror" id="product_id" name="product_id" required>
                                <option value="">— Sélectionner un produit —</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                        data-stock="{{ $product->stock_quantity }}"
                                        data-unit="{{ $product->unit }}"
                                        data-cost="{{ $product->cost_price }}"
                                        {{ (old('product_id', $selectedProductId) == $product->id) ? 'selected' : '' }}>
                                        {{ $product->name }}
                                        @if($product->sku) ({{ $product->sku }}) @endif
                                        — Stock : {{ $product->stock_quantity }} {{ $product->unit }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div id="currentStock" class="form-text d-none">
                                Stock actuel : <strong id="stockValue" class="text-primary">—</strong>
                            </div>
                        </div>

                        {{-- Type --}}
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type de mouvement <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                @foreach($types as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Quantité --}}
                        <div class="col-md-6">
                            <label for="quantity" class="form-label">Quantité <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                   id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="1" step="1" required>
                            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Entrepôt --}}
                        <div class="col-md-6">
                            <label for="site_id" class="form-label">Entrepôt</label>
                            <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                                <option value="">— Sans entrepôt —</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ (old('site_id', $selectedSiteId) == $wh->id) ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Coût unitaire --}}
                        <div class="col-md-6">
                            <label for="unit_cost" class="form-label">Coût unitaire</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('unit_cost') is-invalid @enderror"
                                       id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" step="0.01" min="0">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('unit_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Référence --}}
                        <div class="col-md-6">
                            <label for="reference" class="form-label">Référence</label>
                            <input type="text" class="form-control @error('reference') is-invalid @enderror"
                                   id="reference" name="reference" value="{{ old('reference') }}"
                                   placeholder="N° facture, BL, etc.">
                            @error('reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Raison --}}
                        <div class="col-md-6">
                            <label for="reason" class="form-label">Raison</label>
                            <input type="text" class="form-control @error('reason') is-invalid @enderror"
                                   id="reason" name="reason" value="{{ old('reason') }}"
                                   placeholder="Ex: Livraison fournisseur, Casse, etc.">
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Notes --}}
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Résumé --}}
            <div class="card border-0 shadow-sm mb-4" id="summaryCard" style="display:none;">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Stock actuel</span>
                        <strong id="sumBefore">—</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Mouvement</span>
                        <strong id="sumMovement" class="text-primary">—</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Nouveau stock</span>
                        <strong id="sumAfter" class="fs-5">—</strong>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                    <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('product_id');
    const typeSelect    = document.getElementById('type');
    const qtyInput      = document.getElementById('quantity');
    const costInput     = document.getElementById('unit_cost');
    const summaryCard   = document.getElementById('summaryCard');
    const stockInfo     = document.getElementById('currentStock');
    const stockVal      = document.getElementById('stockValue');
    const sumBefore     = document.getElementById('sumBefore');
    const sumMovement   = document.getElementById('sumMovement');
    const sumAfter      = document.getElementById('sumAfter');

    const outTypes = ['out', 'loss'];

    function updateSummary() {
        const opt = productSelect.options[productSelect.selectedIndex];
        if (!opt || !opt.value) {
            summaryCard.style.display = 'none';
            stockInfo.classList.add('d-none');
            return;
        }
        const stock = parseInt(opt.dataset.stock) || 0;
        const cost  = parseFloat(opt.dataset.cost) || 0;
        const qty   = parseInt(qtyInput.value) || 0;
        const type  = typeSelect.value;
        const isOut = outTypes.includes(type);
        const delta = isOut ? -qty : qty;
        const after = stock + delta;

        stockVal.textContent = stock;
        stockInfo.classList.remove('d-none');

        sumBefore.textContent   = stock;
        sumMovement.textContent = (delta >= 0 ? '+' : '') + delta;
        sumMovement.className   = delta >= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
        sumAfter.textContent    = after;
        sumAfter.className      = 'fs-5 fw-bold ' + (after < 0 ? 'text-danger' : (after === 0 ? 'text-warning' : 'text-success'));
        summaryCard.style.display = '';

        // Pre-fill unit cost from product
        if (!costInput.value && cost > 0) {
            costInput.value = cost.toFixed(2);
        }
    }

    productSelect.addEventListener('change', updateSummary);
    typeSelect.addEventListener('change', updateSummary);
    qtyInput.addEventListener('input', updateSummary);
    updateSummary();
});
</script>
@endpush
