{{-- Quote Form Partial --}}

<form action="{{ isset($quote) ? route('quotes.update', $quote) : route('quotes.store') }}" method="POST" id="quoteForm">
    @csrf
    @if(isset($quote))
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-8">
            {{-- Client Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>Informations client
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if(user_has_feature('save_clients'))
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Sélectionner un client</label>
                            <select class="form-select" id="client_id" name="client_id">
                                <option value="">-- Nouveau client --</option>
                                @foreach($clients ?? [] as $client)
                                    <option value="{{ $client->id }}"
                                        data-name="{{ $client->name }}"
                                        data-email="{{ $client->email }}"
                                        data-phone="{{ $client->phone }}"
                                        data-address="{{ $client->full_address }}"
                                        {{ old('client_id', $quote->client_id ?? request('client_id')) == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="{{ user_has_feature('save_clients') ? 'col-md-6' : 'col-md-6' }}">
                            <label for="client_name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $quote->client_name ?? '') }}">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="client_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="client_email" name="client_email" value="{{ old('client_email', $quote->client_email ?? '') }}">
                        </div>

                        <div class="col-md-6">
                            <label for="client_phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" id="client_phone" name="client_phone" value="{{ old('client_phone', $quote->client_phone ?? '') }}">
                        </div>

                        <div class="col-12">
                            <label for="client_address" class="form-label">Adresse</label>
                            <textarea class="form-control" id="client_address" name="client_address" rows="2">{{ old('client_address', $quote->client_address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quote Items --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Articles
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="discountColToggle" onclick="toggleDiscountColumn()">
                            <i class="fas fa-tag me-1"></i>Remises par ligne
                        </button>
                        @if(user_has_version('standard'))
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#productCatalogModal">
                            <i class="fas fa-box me-1"></i>Depuis le catalogue
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                            <i class="fas fa-plus me-1"></i>Ajouter manuellement
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div id="itemsBody"></div>
                </div>
            </div>

            {{-- Remise globale --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-percent me-2 text-primary"></i>Remise globale
                    </h5>
                </div>
                <div class="card-body py-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div>
                            <label class="form-label small mb-1">Type de remise</label>
                            <select id="globalDiscountType" name="discount_type" class="form-select form-select-sm" style="width:170px;">
                                <option value="" {{ old('discount_type', $quote->discount_type ?? '') == '' ? 'selected' : '' }}>Aucune remise</option>
                                <option value="percent" {{ old('discount_type', $quote->discount_type ?? '') == 'percent' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="amount" {{ old('discount_type', $quote->discount_type ?? '') == 'amount' ? 'selected' : '' }}>Montant fixe (FCFA)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small mb-1">Valeur</label>
                            <div class="input-group input-group-sm" style="width:160px;">
                                <input type="number" id="globalDiscountValue" name="discount_value" class="form-control" value="{{ old('discount_value', $quote->discount_value ?? 0) }}" min="0" step="0.01">
                                <span class="input-group-text" id="discountSuffix">%</span>
                            </div>
                        </div>
                        <div class="align-self-end">
                            <small class="text-muted">Appliquée sur le sous-total HT, avant taxes</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sticky-note me-2 text-primary"></i>Notes et conditions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $quote->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="terms" class="form-label">Conditions</label>
                            <textarea class="form-control" id="terms" name="terms" rows="3">{{ old('terms', $quote->terms ?? 'Devis valable 30 jours. Prix en FCFA, TTC.') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Quote Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Détails devis</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="quote_date" class="form-label">Date du devis <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="quote_date" name="quote_date" value="{{ old('quote_date', isset($quote) ? $quote->quote_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Valide jusqu'au <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="valid_until" name="valid_until" value="{{ old('valid_until', isset($quote) ? $quote->valid_until->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}" required>
                    </div>

                    @if(($sites ?? collect())->count() > 1)
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site</label>
                        <select class="form-select" id="site_id" name="site_id">
                            <option value="">— Aucun —</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ old('site_id', $quote->site_id ?? null) == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if(isset($quote))
                    <div class="mb-3">
                        <label for="status" class="form-label fw-semibold">Statut du devis</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" {{ old('status', $quote->status) == 'draft' ? 'selected' : '' }}>
                                📝 Brouillon (en cours de rédaction)
                            </option>
                            <option value="sent" {{ old('status', $quote->status) == 'sent' ? 'selected' : '' }}>
                                ✅ Validé — prêt à envoyer au client
                            </option>
                        </select>
                        @if($quote->status === 'draft')
                        <div class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Passer à <strong>Validé</strong> finalise le devis. Vous ne pourrez plus le modifier.
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Taxes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Taxes</h5>
                </div>
                <div class="card-body">
                    @foreach($taxes ?? [] as $tax)
                    <div class="form-check mb-2">
                        <input class="form-check-input tax-checkbox" type="checkbox" name="taxes[]" value="{{ $tax->id }}" id="tax_{{ $tax->id }}"
                            data-rate="{{ $tax->rate }}"
                            data-name="{{ $tax->name }}"
                            data-apply="{{ $tax->apply_to }}"
                            {{ in_array($tax->id, old('taxes', isset($quote) ? $quote->taxes->pluck('tax_id')->toArray() : [$tax->id])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="tax_{{ $tax->id }}">
                            {{ $tax->name }} ({{ $tax->rate }}%)
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Code promo --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-tag me-2 text-primary"></i>Code promotionnel</h5>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-sm">
                        <input type="text" id="promoCodeInput" class="form-control text-uppercase"
                               placeholder="Ex: SOLDES20"
                               value="{{ old('promo_code', $quote->promo_code ?? '') }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="applyPromoCode()">
                            <i class="fas fa-check me-1"></i>Appliquer
                        </button>
                    </div>
                    <input type="hidden" name="promo_code" id="promoCodeHidden" value="{{ old('promo_code', $quote->promo_code ?? '') }}">
                    <div id="promoFeedback" class="small mt-2">
                        @if(isset($quote) && $quote->promo_code)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Code <strong>{{ $quote->promo_code }}</strong> appliqué</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Totals --}}
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total HT</span>
                        <span class="fw-semibold" id="subtotalDisplay">0 FCFA</span>
                    </div>
                    <div id="discountRow" class="d-flex justify-content-between mb-1 text-danger" style="display:none">
                        <span>Remise</span>
                        <span id="discountDisplay">0 FCFA</span>
                    </div>
                    <div id="netHtRow" class="d-flex justify-content-between mb-2 fw-semibold border-bottom pb-2" style="display:none">
                        <span>Net HT</span>
                        <span id="netHtDisplay">0 FCFA</span>
                    </div>
                    <div id="taxesDisplay"></div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5 fw-bold">Total TTC</span>
                        <span class="fs-5 fw-bold text-primary" id="totalDisplay">0 FCFA</span>
                    </div>

                    <input type="hidden" name="subtotal" id="subtotal" value="0">
                    <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                    <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>{{ isset($quote) ? 'Mettre à jour' : 'Créer le devis' }}
                        </button>
                        <button type="submit" name="generate_pdf" value="1" class="btn btn-success">
                            <i class="fas fa-file-pdf me-2"></i>Créer et télécharger PDF
                        </button>
                        <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Product Catalog Modal (Standard+ only) --}}
@if(user_has_version('standard'))
<div class="modal fade" id="productCatalogModal" tabindex="-1" aria-labelledby="productCatalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productCatalogModalLabel">
                    <i class="fas fa-box me-2 text-primary"></i>Choisir depuis le catalogue
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="productSearch" placeholder="Rechercher un produit ou service...">
                <div class="row g-3" id="productGrid">
                    @forelse($products ?? [] as $product)
                    <div class="col-md-4 product-card-item"
                         data-id="{{ $product->id }}"
                         data-name="{{ $product->name }}"
                         data-type="{{ $product->type }}"
                         data-price="{{ $product->price }}"
                         data-description="{{ $product->short_description ?? $product->name }}">
                        <div class="card h-100 border product-selectable" style="cursor:pointer;" role="button">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0 me-2">{{ $product->name }}</h6>
                                    <span class="badge bg-{{ $product->type === 'service' ? 'info' : 'warning' }} flex-shrink-0">
                                        {{ $product->type === 'service' ? 'Service' : 'Produit' }}
                                    </span>
                                </div>
                                @if($product->short_description)
                                <p class="text-muted small mb-2">{{ Str::limit($product->short_description, 60) }}</p>
                                @endif
                                <p class="mb-0 fw-bold text-primary">{{ number_format($product->price, 0, ',', ' ') }} FCFA</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2"></i>
                        <p class="mb-0">Aucun produit disponible dans le catalogue.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto"><i class="fas fa-info-circle me-1"></i>Cliquez sur un produit pour l'ajouter au devis</small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endif

<template id="itemTemplate">
    <div class="item-row border rounded mb-2 p-2 bg-white">
        {{-- Ligne 1 : Description + bouton supprimer --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="text-muted fw-semibold item-index-label" style="min-width:20px; font-size:12px;"></span>
            <input type="text" class="form-control form-control-sm item-description flex-grow-1"
                   name="items[INDEX][description]" placeholder="Description de l'article ou du service" required>
            <input type="hidden" name="items[INDEX][product_id]" class="item-product-id">
            <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        {{-- Ligne 2 : Type | Qté | P.U. | [Remise] | Total --}}
        <div class="d-flex align-items-end gap-2 flex-wrap ps-4">
            <div style="min-width:110px; max-width:110px;">
                <label class="form-label small text-muted mb-1">Type</label>
                <select class="form-select form-select-sm item-type" name="items[INDEX][type]">
                    <option value="product">📦 Produit</option>
                    <option value="service">🔧 Service</option>
                </select>
            </div>
            <div style="min-width:90px; max-width:110px;">
                <label class="form-label small text-muted mb-1">Quantité</label>
                <input type="number" class="form-control form-control-sm item-quantity"
                       name="items[INDEX][quantity]" value="1" min="0.01" step="0.01" required>
            </div>
            <div style="min-width:130px; max-width:160px;">
                <label class="form-label small text-muted mb-1">Prix unitaire (FCFA)</label>
                <input type="number" class="form-control form-control-sm item-price"
                       name="items[INDEX][unit_price]" value="0" min="0" step="1" required>
            </div>
            <div class="discount-col" style="min-width:150px; max-width:180px;">
                <label class="form-label small text-muted mb-1">Remise</label>
                <div class="input-group input-group-sm">
                    <select class="form-select form-select-sm item-discount-type"
                            name="items[INDEX][discount_type]" style="max-width:62px; flex-shrink:0;">
                        <option value="">—</option>
                        <option value="percent">%</option>
                        <option value="amount">Mnt</option>
                    </select>
                    <input type="number" class="form-control form-control-sm item-discount-value"
                           name="items[INDEX][discount_value]" value="0" min="0" step="0.01">
                </div>
                <input type="hidden" name="items[INDEX][discount_amount]" class="item-discount-amount" value="0">
            </div>
            <div class="ms-auto text-end">
                <label class="form-label small text-muted mb-1">Total HT</label>
                <div class="fw-bold text-primary item-total" style="font-size:1.05rem; white-space:nowrap;">0 FCFA</div>
                <input type="hidden" name="items[INDEX][total]" class="item-total-input" value="0">
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
let itemIndex = 0;
let discountColumnVisible = false;
const existingItems = @json(old('items', isset($quote) ? $quote->items->toArray() : []));

function toggleDiscountColumn(forceShow = null) {
    discountColumnVisible = forceShow !== null ? forceShow : !discountColumnVisible;
    const btn = document.getElementById('discountColToggle');

    document.querySelectorAll('.discount-col').forEach(el => {
        el.style.display = discountColumnVisible ? '' : 'none';
    });

    if (btn) {
        if (discountColumnVisible) {
            btn.innerHTML = '<i class="fas fa-times me-1"></i>Masquer remises ligne';
            btn.classList.replace('btn-outline-secondary', 'btn-outline-warning');
        } else {
            btn.innerHTML = '<i class="fas fa-tag me-1"></i>Remises par ligne';
            btn.classList.replace('btn-outline-warning', 'btn-outline-secondary');
            // Réinitialiser toutes les remises ligne
            document.querySelectorAll('.item-row').forEach(row => {
                row.querySelector('.item-discount-type').value = '';
                row.querySelector('.item-discount-value').value = 0;
                row.querySelector('.item-discount-amount').value = 0;
            });
            calculateTotals();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Afficher la colonne remise si des lignes ont déjà une remise
    const hasExistingLineDiscount = existingItems.some(
        item => item.discount_type && item.discount_type !== '' && item.discount_type !== null
    );

    if (existingItems.length > 0) {
        existingItems.forEach(item => addItem(item));
    } else {
        addItem();
    }

    if (hasExistingLineDiscount) {
        toggleDiscountColumn(true);
    }

    @if(user_has_feature('save_clients'))
    const clientSelect = document.getElementById('client_id');
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            const option = this.options[this.selectedIndex];
            if (option.value) {
                document.getElementById('client_name').value = option.dataset.name || '';
                document.getElementById('client_email').value = option.dataset.email || '';
                document.getElementById('client_phone').value = option.dataset.phone || '';
                document.getElementById('client_address').value = option.dataset.address || '';
            }
        });
    }
    @endif

    document.querySelectorAll('.tax-checkbox').forEach(cb => {
        cb.addEventListener('change', calculateTotals);
    });

    const gType  = document.getElementById('globalDiscountType');
    const gValue = document.getElementById('globalDiscountValue');
    gType.addEventListener('change', function() {
        updateDiscountSuffix();
        calculateTotals();
    });
    gValue.addEventListener('input', calculateTotals);
    updateDiscountSuffix();

    calculateTotals();
});

function updateDiscountSuffix() {
    const suffix = document.getElementById('discountSuffix');
    const type   = document.getElementById('globalDiscountType').value;
    suffix.textContent = type === 'percent' ? '%' : (type === 'amount' ? 'FCFA' : '—');
}

function addItem(data = null) {
    const template = document.getElementById('itemTemplate');
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('.item-row');

    row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);

    if (data) {
        row.querySelector('.item-description').value = data.description || '';
        row.querySelector('.item-type').value = data.type || 'product';
        row.querySelector('.item-quantity').value = data.quantity || 1;
        row.querySelector('.item-price').value = data.unit_price || 0;
        if (data.product_id) {
            row.querySelector('.item-product-id').value = data.product_id;
        }
        if (data.discount_type) {
            row.querySelector('.item-discount-type').value = data.discount_type;
        }
        if (data.discount_value) {
            row.querySelector('.item-discount-value').value = data.discount_value;
        }
    }

    row.querySelector('.item-quantity').addEventListener('input', () => calculateRowTotal(row));
    row.querySelector('.item-price').addEventListener('input', () => calculateRowTotal(row));
    row.querySelector('.item-type').addEventListener('change', calculateTotals);
    row.querySelector('.item-discount-type').addEventListener('change', () => calculateRowTotal(row));
    row.querySelector('.item-discount-value').addEventListener('input', () => calculateRowTotal(row));

    document.getElementById('itemsBody').appendChild(row);

    // Appliquer la visibilité actuelle de la colonne remise
    const discountCell = row.querySelector('.discount-col');
    if (discountCell) {
        discountCell.style.display = discountColumnVisible ? '' : 'none';
    }

    itemIndex++;
    calculateRowTotal(row);
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    }
}

function calculateRowTotal(row) {
    const qty   = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const dtype = row.querySelector('.item-discount-type').value;
    const dval  = parseFloat(row.querySelector('.item-discount-value').value) || 0;
    const gross = qty * price;

    let disc = 0;
    if (dtype === 'percent') disc = gross * dval / 100;
    else if (dtype === 'amount') disc = Math.min(dval, gross);

    const total = Math.max(0, gross - disc);

    row.querySelector('.item-total').textContent = formatNumber(total) + ' FCFA';
    row.querySelector('.item-total-input').value = Math.round(total * 100) / 100;
    row.querySelector('.item-discount-amount').value = Math.round(disc * 100) / 100;

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0, productTotal = 0, serviceTotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const total = parseFloat(row.querySelector('.item-total-input').value) || 0;
        const type  = row.querySelector('.item-type').value;
        subtotal += total;
        if (type === 'product') productTotal += total;
        else serviceTotal += total;
    });

    // Remise globale
    const gtype = document.getElementById('globalDiscountType').value;
    const gval  = parseFloat(document.getElementById('globalDiscountValue').value) || 0;
    let globalDisc = 0;
    if (gtype === 'percent') globalDisc = subtotal * gval / 100;
    else if (gtype === 'amount') globalDisc = Math.min(gval, subtotal);

    const netHT = Math.max(0, subtotal - globalDisc);
    const ratio = subtotal > 0 ? netHT / subtotal : 1;

    // Taxes proratisées sur le Net HT
    let totalTax = 0, taxesHtml = '';
    document.querySelectorAll('.tax-checkbox:checked').forEach(cb => {
        const rate    = parseFloat(cb.dataset.rate);
        const name    = cb.dataset.name;
        const applyTo = cb.dataset.apply;

        let taxBase = netHT;
        if (applyTo === 'products') taxBase = productTotal * ratio;
        if (applyTo === 'services') taxBase = serviceTotal * ratio;

        const taxAmount = taxBase * rate / 100;
        totalTax += taxAmount;
        taxesHtml += `<div class="d-flex justify-content-between mb-1"><span class="text-muted">${name}</span><span>${formatNumber(taxAmount)} FCFA</span></div>`;
    });

    const total = netHT + totalTax;

    // Affichage
    document.getElementById('subtotalDisplay').textContent = formatNumber(subtotal) + ' FCFA';

    const discRow = document.getElementById('discountRow');
    const netRow  = document.getElementById('netHtRow');
    if (globalDisc > 0) {
        discRow.style.display = 'flex';
        netRow.style.display  = 'flex';
        document.getElementById('discountDisplay').textContent = '−' + formatNumber(globalDisc) + ' FCFA';
        document.getElementById('netHtDisplay').textContent    = formatNumber(netHT) + ' FCFA';
    } else {
        discRow.style.display = 'none';
        netRow.style.display  = 'none';
    }

    document.getElementById('taxesDisplay').innerHTML  = taxesHtml;
    document.getElementById('totalDisplay').textContent = formatNumber(total) + ' FCFA';

    document.getElementById('subtotal').value         = Math.round(subtotal * 100) / 100;
    document.getElementById('discount_amount').value  = Math.round(globalDisc * 100) / 100;
    document.getElementById('tax_amount').value       = Math.round(totalTax * 100) / 100;
    document.getElementById('total_amount').value     = Math.round(total * 100) / 100;
}

function applyPromoCode() {
    const code   = document.getElementById('promoCodeInput').value.trim().toUpperCase();
    const amount = parseFloat(document.getElementById('subtotal').value) || 0;
    const feedback = document.getElementById('promoFeedback');

    if (!code) {
        feedback.innerHTML = '<span class="text-warning">Veuillez saisir un code promotionnel.</span>';
        return;
    }

    feedback.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin me-1"></i>Vérification...</span>';

    fetch(`{{ route('promotions.apply') }}?code=${encodeURIComponent(code)}&amount=${amount}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('globalDiscountType').value  = data.discount_type;
            document.getElementById('globalDiscountValue').value = data.discount_value;
            document.getElementById('promoCodeHidden').value     = code;
            document.getElementById('promoCodeInput').value      = code;
            updateDiscountSuffix();
            calculateTotals();
            const suffix = data.discount_type === 'percent' ? '%' : ' FCFA';
            feedback.innerHTML = `<span class="text-success"><i class="fas fa-check-circle me-1"></i><strong>${data.name}</strong> appliquée (−${data.discount_value}${suffix})</span>`;
        } else {
            feedback.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle me-1"></i>${data.message}</span>`;
        }
    })
    .catch(() => {
        feedback.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Erreur lors de la vérification.</span>';
    });
}

function formatNumber(num) {
    return Math.round(num).toLocaleString('fr-FR');
}

@if(user_has_version('standard'))
// Product catalog search
const productSearch = document.getElementById('productSearch');
if (productSearch) {
    productSearch.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('.product-card-item').forEach(function(card) {
            const name = card.dataset.name.toLowerCase();
            const desc = card.dataset.description.toLowerCase();
            card.style.display = (name.includes(term) || desc.includes(term)) ? '' : 'none';
        });
    });
}

// Product card click to add to quote
document.querySelectorAll('.product-selectable').forEach(function(card) {
    card.addEventListener('click', function() {
        const parent = this.closest('.product-card-item');
        addItem({
            product_id: parent.dataset.id,
            description: parent.dataset.name,
            type: parent.dataset.type,
            quantity: 1,
            unit_price: parseFloat(parent.dataset.price) || 0,
        });
        // Visual feedback
        this.classList.add('border-success');
        setTimeout(() => this.classList.remove('border-success'), 800);
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('productCatalogModal')).hide();
        // Reset search
        productSearch.value = '';
        document.querySelectorAll('.product-card-item').forEach(c => c.style.display = '');
    });
});
@endif
</script>
@endpush
