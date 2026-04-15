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
            {{-- Infos communes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-exchange-alt me-2 text-primary"></i>Détails du mouvement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
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
                                      id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Lignes produits --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Produits concernés</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary"
                                data-bs-toggle="modal" data-bs-target="#productCatalogModal">
                            <i class="fas fa-th me-1"></i>Choisir depuis le catalogue
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="addRowBtn">
                            <i class="fas fa-plus me-1"></i>Ajouter une ligne
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @error('items')<div class="alert alert-danger m-3">{{ $message }}</div>@enderror
                    @error('items.*.product_id')<div class="alert alert-danger m-3">{{ $message }}</div>@enderror

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45%">Produit <span class="text-danger">*</span></th>
                                    <th style="width:15%">Stock actuel</th>
                                    <th style="width:15%">Quantité <span class="text-danger">*</span></th>
                                    <th style="width:20%">Coût unitaire</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @php
                                    $oldItems = old('items', [['product_id' => $selectedProductId, 'quantity' => 1, 'unit_cost' => null]]);
                                @endphp
                                @foreach($oldItems as $i => $row)
                                    <tr class="item-row">
                                        <td>
                                            <select class="form-select form-select-sm product-select" name="items[{{ $i }}][product_id]" required>
                                                <option value="">— Choisir —</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}"
                                                        data-stock="{{ $product->stock_quantity }}"
                                                        data-unit="{{ $product->unit }}"
                                                        data-cost="{{ $product->cost_price }}"
                                                        {{ ($row['product_id'] ?? null) == $product->id ? 'selected' : '' }}>
                                                        {{ $product->name }}@if($product->sku) ({{ $product->sku }})@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <span class="stock-display text-muted">—</span>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm qty-input"
                                                   name="items[{{ $i }}][quantity]"
                                                   value="{{ $row['quantity'] ?? 1 }}" min="1" step="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" class="form-control cost-input"
                                                       name="items[{{ $i }}][unit_cost]"
                                                       value="{{ $row['unit_cost'] ?? '' }}" step="0.01" min="0">
                                                <span class="input-group-text">FCFA</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Supprimer">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Résumé global --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Résumé</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Nombre de produits</span>
                        <strong id="sumCount">0</strong>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Quantité totale</span>
                        <strong id="sumQty" class="text-primary">0</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Type</span>
                        <strong id="sumType">—</strong>
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

    {{-- Template ligne (pour JS) --}}
    <template id="rowTemplate">
        <tr class="item-row">
            <td>
                <select class="form-select form-select-sm product-select" name="items[__INDEX__][product_id]" required>
                    <option value="">— Choisir —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            data-stock="{{ $product->stock_quantity }}"
                            data-unit="{{ $product->unit }}"
                            data-cost="{{ $product->cost_price }}">
                            {{ $product->name }}@if($product->sku) ({{ $product->sku }})@endif
                        </option>
                    @endforeach
                </select>
            </td>
            <td><span class="stock-display text-muted">—</span></td>
            <td>
                <input type="number" class="form-control form-control-sm qty-input"
                       name="items[__INDEX__][quantity]" value="1" min="1" step="1" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" class="form-control cost-input"
                           name="items[__INDEX__][unit_cost]" step="0.01" min="0">
                    <span class="input-group-text">FCFA</span>
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Supprimer">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    </template>
</form>

{{-- Modal Catalogue Produits --}}
<div class="modal fade" id="productCatalogModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-box me-2 text-primary"></i>Choisir depuis le catalogue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex gap-2 mb-3">
                    <input type="text" class="form-control" id="catalogSearch"
                           placeholder="Rechercher un produit (nom, SKU)...">
                    <button type="button" class="btn btn-outline-secondary" id="catalogSelectAll">
                        <i class="fas fa-check-double me-1"></i>Tout
                    </button>
                </div>
                <div class="row g-2" id="catalogGrid">
                    @forelse($products as $product)
                        <div class="col-md-6 catalog-item"
                             data-id="{{ $product->id }}"
                             data-name="{{ strtolower($product->name . ' ' . ($product->sku ?? '')) }}"
                             data-stock="{{ $product->stock_quantity }}"
                             data-unit="{{ $product->unit }}"
                             data-cost="{{ $product->cost_price }}"
                             data-product-name="{{ $product->name }}"
                             data-product-sku="{{ $product->sku }}">
                            <div class="card h-100 border catalog-card" style="cursor:pointer;">
                                <div class="card-body p-2">
                                    <div class="form-check">
                                        <input class="form-check-input catalog-check" type="checkbox" id="cat-{{ $product->id }}">
                                        <label class="form-check-label w-100" for="cat-{{ $product->id }}">
                                            <div class="fw-semibold">{{ $product->name }}</div>
                                            @if($product->sku)
                                                <small class="text-muted d-block">SKU : {{ $product->sku }}</small>
                                            @endif
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-warehouse me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }}
                                                </small>
                                                @if($product->cost_price > 0)
                                                    <small class="text-muted">{{ number_format($product->cost_price, 0, ',', ' ') }} FCFA</small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            <i class="fas fa-box-open fa-2x mb-2"></i>
                            <p class="mb-0">Aucun produit suivi en stock dans le catalogue.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">
                    <span id="catalogCount">0</span> produit(s) sélectionné(s)
                </small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="catalogAddBtn">
                    <i class="fas fa-plus me-1"></i>Ajouter à la liste
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tbody       = document.getElementById('itemsBody');
    const addBtn      = document.getElementById('addRowBtn');
    const tpl         = document.getElementById('rowTemplate');
    const typeSelect  = document.getElementById('type');
    const sumCount    = document.getElementById('sumCount');
    const sumQty      = document.getElementById('sumQty');
    const sumType     = document.getElementById('sumType');

    let nextIndex = tbody.querySelectorAll('tr.item-row').length;

    function refreshRow(row) {
        const select = row.querySelector('.product-select');
        const stockDisplay = row.querySelector('.stock-display');
        const costInput = row.querySelector('.cost-input');
        const opt = select.options[select.selectedIndex];

        if (opt && opt.value) {
            const stock = parseInt(opt.dataset.stock) || 0;
            const unit  = opt.dataset.unit || '';
            const cost  = parseFloat(opt.dataset.cost) || 0;
            stockDisplay.textContent = stock + ' ' + unit;
            stockDisplay.className = 'stock-display ' + (stock > 0 ? 'text-success' : 'text-warning') + ' fw-bold';
            if (!costInput.value && cost > 0) {
                costInput.value = cost.toFixed(2);
            }
        } else {
            stockDisplay.textContent = '—';
            stockDisplay.className = 'stock-display text-muted';
        }
    }

    function refreshSummary() {
        const rows = tbody.querySelectorAll('tr.item-row');
        let count = 0, totalQty = 0;
        rows.forEach(r => {
            const sel = r.querySelector('.product-select');
            if (sel && sel.value) {
                count++;
                totalQty += parseInt(r.querySelector('.qty-input').value) || 0;
            }
        });
        sumCount.textContent = count;
        sumQty.textContent = totalQty;
        const isOut = ['out', 'loss'].includes(typeSelect.value);
        sumQty.className = 'fw-bold ' + (isOut ? 'text-danger' : 'text-success');
        sumType.textContent = typeSelect.options[typeSelect.selectedIndex]?.text || '—';
    }

    function bindRow(row) {
        row.querySelector('.product-select').addEventListener('change', () => { refreshRow(row); refreshSummary(); });
        row.querySelector('.qty-input').addEventListener('input', refreshSummary);
        row.querySelector('.remove-row').addEventListener('click', () => {
            if (tbody.querySelectorAll('tr.item-row').length > 1) {
                row.remove();
                refreshSummary();
            } else {
                // Reset au lieu de supprimer la dernière ligne
                row.querySelector('.product-select').value = '';
                row.querySelector('.qty-input').value = 1;
                row.querySelector('.cost-input').value = '';
                refreshRow(row);
                refreshSummary();
            }
        });
    }

    addBtn.addEventListener('click', function () {
        const html = tpl.innerHTML.replace(/__INDEX__/g, nextIndex++);
        const tr = document.createElement('tbody');
        tr.innerHTML = html;
        const newRow = tr.firstElementChild;
        tbody.appendChild(newRow);
        bindRow(newRow);
        refreshSummary();
    });

    typeSelect.addEventListener('change', refreshSummary);

    // Init existing rows
    tbody.querySelectorAll('tr.item-row').forEach(row => {
        bindRow(row);
        refreshRow(row);
    });
    refreshSummary();

    // ===== Catalogue Modal =====
    const catalogGrid    = document.getElementById('catalogGrid');
    const catalogSearch  = document.getElementById('catalogSearch');
    const catalogCount   = document.getElementById('catalogCount');
    const catalogAddBtn  = document.getElementById('catalogAddBtn');
    const catalogSelectAll = document.getElementById('catalogSelectAll');
    const modalEl        = document.getElementById('productCatalogModal');

    function updateCatalogCount() {
        const n = catalogGrid.querySelectorAll('.catalog-check:checked').length;
        catalogCount.textContent = n;
    }

    // Click on card toggles checkbox
    catalogGrid.querySelectorAll('.catalog-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'LABEL') return;
            const cb = this.querySelector('.catalog-check');
            cb.checked = !cb.checked;
            updateCatalogCount();
        });
    });
    catalogGrid.querySelectorAll('.catalog-check').forEach(cb => {
        cb.addEventListener('change', updateCatalogCount);
    });

    // Search filter
    catalogSearch.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        catalogGrid.querySelectorAll('.catalog-item').forEach(item => {
            item.style.display = item.dataset.name.includes(q) ? '' : 'none';
        });
    });

    // Select all visible
    catalogSelectAll.addEventListener('click', function () {
        const visible = Array.from(catalogGrid.querySelectorAll('.catalog-item'))
            .filter(i => i.style.display !== 'none');
        const allChecked = visible.every(i => i.querySelector('.catalog-check').checked);
        visible.forEach(i => { i.querySelector('.catalog-check').checked = !allChecked; });
        updateCatalogCount();
    });

    // Get IDs already in the table
    function existingProductIds() {
        const ids = new Set();
        tbody.querySelectorAll('.product-select').forEach(sel => {
            if (sel.value) ids.add(sel.value);
        });
        return ids;
    }

    // Find first empty row (no product selected)
    function firstEmptyRow() {
        return Array.from(tbody.querySelectorAll('tr.item-row')).find(r => !r.querySelector('.product-select').value);
    }

    // Add a product to the items table
    function addProductToTable(productId) {
        // Skip if already in the table
        if (existingProductIds().has(String(productId))) return false;

        // Reuse first empty row, otherwise create a new one
        let row = firstEmptyRow();
        if (!row) {
            const html = tpl.innerHTML.replace(/__INDEX__/g, nextIndex++);
            const tmp = document.createElement('tbody');
            tmp.innerHTML = html;
            row = tmp.firstElementChild;
            tbody.appendChild(row);
            bindRow(row);
        }
        row.querySelector('.product-select').value = String(productId);
        refreshRow(row);
        return true;
    }

    // Add selected products
    catalogAddBtn.addEventListener('click', function () {
        const checked = catalogGrid.querySelectorAll('.catalog-check:checked');
        if (checked.length === 0) return;

        let added = 0, skipped = 0;
        checked.forEach(cb => {
            const item = cb.closest('.catalog-item');
            if (addProductToTable(item.dataset.id)) {
                added++;
            } else {
                skipped++;
            }
            cb.checked = false;
        });
        updateCatalogCount();
        refreshSummary();

        // Close modal
        bootstrap.Modal.getInstance(modalEl)?.hide();
    });

    // Reset search/selection on modal open
    modalEl.addEventListener('show.bs.modal', function () {
        catalogSearch.value = '';
        catalogGrid.querySelectorAll('.catalog-item').forEach(i => i.style.display = '');
        catalogGrid.querySelectorAll('.catalog-check').forEach(cb => cb.checked = false);
        updateCatalogCount();
    });
});
</script>
@endpush
