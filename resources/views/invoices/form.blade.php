{{-- Invoice Form Partial --}}

<form action="{{ isset($invoice) ? route('invoices.update', $invoice) : route('invoices.store') }}" method="POST" id="invoiceForm">
    @csrf
    @if(isset($invoice))
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
                        {{-- Client Select --}}
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Sélectionner un client existant</label>
                            <select class="form-select @error('client_id') is-invalid @enderror" id="client_id" name="client_id">
                                <option value="">-- Nouveau client --</option>
                                @foreach($clients ?? [] as $client)
                                    <option value="{{ $client->id }}"
                                        data-name="{{ $client->name }}"
                                        data-email="{{ $client->email }}"
                                        data-phone="{{ $client->phone }}"
                                        data-address="{{ $client->full_address }}"
                                        {{ old('client_id', $invoice->client_id ?? request('client_id')) == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Client Name --}}
                        <div class="col-md-6">
                            <label for="client_name" class="form-label">Nom du client</label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $invoice->client_name ?? '') }}">
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Client Email --}}
                        <div class="col-md-6">
                            <label for="client_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('client_email') is-invalid @enderror" id="client_email" name="client_email" value="{{ old('client_email', $invoice->client_email ?? '') }}">
                            @error('client_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Client Phone --}}
                        <div class="col-md-6">
                            <label for="client_phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('client_phone') is-invalid @enderror" id="client_phone" name="client_phone" value="{{ old('client_phone', $invoice->client_phone ?? '') }}">
                            @error('client_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Client Address --}}
                        <div class="col-12">
                            <label for="client_address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('client_address') is-invalid @enderror" id="client_address" name="client_address" rows="2">{{ old('client_address', $invoice->client_address ?? '') }}</textarea>
                            @error('client_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if(user_has_feature('save_clients') && auth()->user()->can('clients.create'))
                        {{-- Bouton enregistrer client --}}
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="quickClientHint">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Si le client n'existe pas dans votre base, vous pouvez l'enregistrer en un clic.
                                </small>
                                <button type="button" class="btn btn-sm btn-outline-success"
                                        id="quickSaveClientBtn"
                                        data-url="{{ route('clients.quick-store') }}">
                                    <i class="fas fa-user-plus me-1"></i>Enregistrer le client
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Objet / Sujet --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="subject" class="form-label">Objet de la facture</label>
                    <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject"
                           value="{{ old('subject', $invoice->subject ?? '') }}"
                           placeholder="Ex: Prestation de services informatiques - Mars 2026">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Optionnel. S'affiche en haut du document PDF.</div>
                </div>
            </div>

            {{-- Alerte globale stock --}}
            <div id="stockGlobalAlert" class="alert alert-danger d-none mb-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Stock insuffisant :</strong> Corrigez ou retirez les lignes en rouge avant de soumettre la facture.
            </div>

            {{-- Invoice Items --}}
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
                                <option value="" {{ old('discount_type', $invoice->discount_type ?? '') == '' ? 'selected' : '' }}>Aucune remise</option>
                                <option value="percent" {{ old('discount_type', $invoice->discount_type ?? '') == 'percent' ? 'selected' : '' }}>Pourcentage (%)</option>
                                <option value="amount" {{ old('discount_type', $invoice->discount_type ?? '') == 'amount' ? 'selected' : '' }}>Montant fixe (FCFA)</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label small mb-1">Valeur</label>
                            <div class="input-group input-group-sm" style="width:160px;">
                                <input type="number" id="globalDiscountValue" name="discount_value" class="form-control" value="{{ old('discount_value', $invoice->discount_value ?? 0) }}" min="0" step="0.01">
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
                            <label for="notes" class="form-label">Notes (visible sur la facture)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $invoice->notes ?? '') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="terms" class="form-label">Conditions de paiement</label>
                            <textarea class="form-control" id="terms" name="terms" rows="3">{{ old('terms', $invoice->terms ?? 'Paiement à réception de facture.') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Invoice Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Détails facture</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="invoice_date" class="form-label">Date de facturation <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('invoice_date') is-invalid @enderror" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', isset($invoice) ? $invoice->invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('invoice_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="due_date" class="form-label">Date d'échéance <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', isset($invoice) ? $invoice->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}" required>
                        @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(($sites ?? collect())->count() > 1)
                    <div class="mb-3">
                        <label for="site_id" class="form-label">Site</label>
                        <select class="form-select" id="site_id" name="site_id">
                            <option value="">— Aucun —</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ old('site_id', $invoice->site_id ?? null) == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if(isset($invoice))
                    <div class="mb-3">
                        <label for="status" class="form-label">Statut</label>
                        <select class="form-select" id="status" name="status">
                            <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Brouillon</option>
                            <option value="sent" {{ $invoice->status == 'sent' ? 'selected' : '' }}>Envoyée</option>
                            <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Taxes --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Taxes applicables</h5>
                </div>
                <div class="card-body">
                    @foreach($taxes ?? [] as $tax)
                    <div class="form-check mb-2">
                        <input class="form-check-input tax-checkbox" type="checkbox" name="taxes[]" value="{{ $tax->id }}" id="tax_{{ $tax->id }}"
                            data-rate="{{ $tax->rate }}"
                            data-name="{{ $tax->name }}"
                            data-apply="{{ $tax->apply_to }}"
                            {{ in_array($tax->id, old('taxes', isset($invoice) ? $invoice->taxes->pluck('tax_id')->toArray() : [$tax->id])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="tax_{{ $tax->id }}">
                            {{ $tax->name }} ({{ $tax->rate }}%)
                            <small class="text-muted d-block">{{ $tax->apply_to_label }}</small>
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
                               value="{{ old('promo_code', $invoice->promo_code ?? '') }}">
                        <button type="button" class="btn btn-outline-secondary" onclick="applyPromoCode()">
                            <i class="fas fa-check me-1"></i>Appliquer
                        </button>
                    </div>
                    <input type="hidden" name="promo_code" id="promoCodeHidden" value="{{ old('promo_code', $invoice->promo_code ?? '') }}">
                    <div id="promoFeedback" class="small mt-2">
                        @if(isset($invoice) && $invoice->promo_code)
                            <span class="text-success"><i class="fas fa-check-circle me-1"></i>Code <strong>{{ $invoice->promo_code }}</strong> appliqué</span>
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
                    <div id="taxesDisplay">
                        {{-- Tax lines will be added dynamically --}}
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fs-5 fw-bold">Total TTC</span>
                        <span class="fs-5 fw-bold text-primary" id="totalDisplay">0 FCFA</span>
                    </div>

                    {{-- Hidden fields for totals --}}
                    <input type="hidden" name="subtotal" id="subtotal" value="0">
                    <input type="hidden" name="discount_amount" id="discount_amount" value="0">
                    <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>
            </div>

            {{-- Options à la création (paiement + livraison immédiats) --}}
            @unless(isset($invoice))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-warning"></i>Options à la création
                    </h6>
                </div>
                <div class="card-body">
                    {{-- Paiement immédiat --}}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="fw-semibold mb-0" for="payment_immediate">
                            <i class="fas fa-money-bill-wave me-1 text-success"></i>Paiement immédiat
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="payment_immediate"
                                   name="payment_immediate" value="1" checked>
                        </div>
                    </div>
                    <div id="paymentOptions" class="mb-3 ps-2">
                        <select name="payment_method" id="payment_method"
                                class="form-select form-select-sm mb-2" onchange="togglePaymentRef()">
                            <option value="cash">💵 Espèces</option>
                            <option value="check">📄 Chèque</option>
                            <option value="bank_transfer">🏦 Virement bancaire</option>
                            <option value="credit_card">💳 Carte bancaire</option>
                            <option value="mobile_money">📱 Mobile Money</option>
                            <option value="other">Autre</option>
                        </select>
                        <div id="paymentRefDiv" style="display:none">
                            <input type="text" name="payment_reference"
                                   class="form-control form-control-sm"
                                   placeholder="Référence / N° transaction">
                        </div>
                    </div>

                    <hr class="my-2">

                    {{-- Livraison immédiate --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="fw-semibold mb-0" for="delivery_immediate">
                            <i class="fas fa-truck me-1 text-primary"></i>Livraison immédiate
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="delivery_immediate"
                                   name="delivery_immediate" value="1" checked>
                        </div>
                    </div>
                    <small class="text-muted">Un bon de livraison sera créé automatiquement.</small>

                    <hr class="my-2">

                    {{-- Code secret de livraison --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="fw-semibold mb-0" for="generate_delivery_pin">
                            <i class="fas fa-key me-1 text-warning"></i>Code secret de livraison
                        </label>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="generate_delivery_pin"
                                   name="generate_delivery_pin" value="1"
                                   {{ old('generate_delivery_pin') ? 'checked' : '' }}>
                        </div>
                    </div>
                    <small class="text-muted">
                        Un code à 8 caractères sera généré et imprimé sur la facture. Le client devra le communiquer au livreur pour valider la livraison.
                    </small>
                </div>
            </div>
            @endunless

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>{{ isset($invoice) ? 'Mettre à jour' : 'Créer la facture' }}
                        </button>
                        <button type="submit" name="generate_pdf" value="1" class="btn btn-success">
                            <i class="fas fa-file-pdf me-2"></i>Créer et télécharger PDF
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
                    @php
                        $outOfStock = $product->type === 'product' && $product->track_inventory && ($product->stock_quantity ?? 0) <= 0;
                    @endphp
                    <div class="col-md-4 product-card-item"
                         data-id="{{ $product->id }}"
                         data-name="{{ $product->name }}"
                         data-type="{{ $product->type }}"
                         data-price="{{ $product->price }}"
                         data-description="{{ $product->short_description ?? $product->name }}"
                         data-stock="{{ $product->stock_quantity ?? 0 }}"
                         data-track="{{ $product->track_inventory ? '1' : '0' }}">
                        <div class="card h-100 border product-selectable{{ $outOfStock ? ' opacity-50' : '' }}"
                             style="{{ $outOfStock ? 'cursor:not-allowed;' : 'cursor:pointer;' }}" role="button">
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
                                @if($product->type === 'product' && $product->track_inventory)
                                    @if($outOfStock)
                                        <span class="badge bg-danger mt-1"><i class="fas fa-times-circle me-1"></i>Rupture de stock</span>
                                    @else
                                        <small class="text-muted mt-1 d-block"><i class="fas fa-warehouse me-1"></i>Stock : {{ $product->stock_quantity }}</small>
                                    @endif
                                @endif
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
                <small class="text-muted me-auto"><i class="fas fa-info-circle me-1"></i>Cliquez sur un produit pour l'ajouter à la facture</small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Item Row Template --}}
<template id="itemTemplate">
    <div class="item-row border rounded mb-2 p-2 bg-white">
        {{-- Ligne 1 : Description + bouton supprimer --}}
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="text-muted fw-semibold item-index-label" style="min-width:20px; font-size:12px;"></span>
            <input type="text" class="form-control form-control-sm item-description flex-grow-1"
                   name="items[INDEX][description]" placeholder="Description de l'article ou du service" required>
            <input type="hidden" name="items[INDEX][product_id]" class="item-product-id">
            <input type="hidden" name="items[INDEX][stock_quantity]" class="item-stock-qty" value="0">
            <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </div>
        {{-- Alerte stock --}}
        <div class="item-stock-alert alert alert-danger py-1 px-2 mb-2 d-none" style="font-size:12px; margin-left:28px;">
            <i class="fas fa-exclamation-triangle me-1"></i><span class="item-stock-alert-msg"></span>
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
                <small class="item-stock-info text-muted" style="font-size:10px;"></small>
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
const existingItems = @json(old('items', isset($invoice) ? $invoice->items->toArray() : []));
const productStockMap = @json(collect($products ?? [])->mapWithKeys(fn($p) => [$p->id => ['stock_quantity' => (float)($p->stock_quantity ?? 0), 'track_inventory' => (bool)$p->track_inventory]]));

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

    // Quick-save client : enregistre les infos saisies manuellement comme nouveau client
    const quickSaveBtn = document.getElementById('quickSaveClientBtn');
    if (quickSaveBtn) {
        quickSaveBtn.addEventListener('click', function () {
            const nameInput = document.getElementById('client_name');
            const name = nameInput.value.trim();
            if (!name) {
                nameInput.classList.add('is-invalid');
                nameInput.focus();
                return;
            }
            nameInput.classList.remove('is-invalid');

            const payload = {
                name:    name,
                email:   document.getElementById('client_email').value.trim() || null,
                phone:   document.getElementById('client_phone').value.trim() || null,
                address: document.getElementById('client_address').value.trim() || null,
            };

            const originalHtml = quickSaveBtn.innerHTML;
            quickSaveBtn.disabled = true;
            quickSaveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Enregistrement...';

            fetch(quickSaveBtn.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                  || document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify(payload),
            })
            .then(async (res) => {
                const json = await res.json();
                if (!res.ok) throw new Error(json.message || 'Erreur lors de l\'enregistrement.');
                return json;
            })
            .then((data) => {
                const c = data.client;
                // Ajouter (ou remplacer) l'option dans le select
                if (clientSelect) {
                    let opt = clientSelect.querySelector('option[value="' + c.id + '"]');
                    if (!opt) {
                        opt = document.createElement('option');
                        opt.value = c.id;
                        clientSelect.appendChild(opt);
                    }
                    opt.textContent = c.name;
                    opt.dataset.name = c.name;
                    opt.dataset.email = c.email || '';
                    opt.dataset.phone = c.phone || '';
                    opt.dataset.address = c.full_address || '';
                    clientSelect.value = c.id;
                }

                // Feedback visuel
                quickSaveBtn.classList.remove('btn-outline-success');
                quickSaveBtn.classList.add('btn-success');
                quickSaveBtn.innerHTML = '<i class="fas fa-check me-1"></i>' + (data.duplicate ? 'Déjà existant' : 'Enregistré');
                const hint = document.getElementById('quickClientHint');
                if (hint) {
                    hint.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i>' + data.message;
                }
                setTimeout(() => {
                    quickSaveBtn.disabled = false;
                    quickSaveBtn.classList.remove('btn-success');
                    quickSaveBtn.classList.add('btn-outline-success');
                    quickSaveBtn.innerHTML = originalHtml;
                }, 2500);
            })
            .catch((err) => {
                quickSaveBtn.disabled = false;
                quickSaveBtn.innerHTML = originalHtml;
                alert(err.message || 'Erreur lors de l\'enregistrement du client.');
            });
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
            const stockInfo = productStockMap[data.product_id];
            if (stockInfo) {
                row.querySelector('.item-stock-qty').value = stockInfo.stock_quantity;
                if (stockInfo.track_inventory) {
                    const stockInfoEl = row.querySelector('.item-stock-info');
                    if (stockInfoEl) stockInfoEl.textContent = 'Stock : ' + stockInfo.stock_quantity;
                }
            }
        }
        if (data.discount_type) {
            row.querySelector('.item-discount-type').value = data.discount_type;
        }
        if (data.discount_value) {
            row.querySelector('.item-discount-value').value = data.discount_value;
        }
    }

    row.querySelector('.item-quantity').addEventListener('input', () => { calculateRowTotal(row); validateRowStock(row); });
    row.querySelector('.item-price').addEventListener('input', () => calculateRowTotal(row));
    row.querySelector('.item-type').addEventListener('change', () => { calculateTotals(); validateRowStock(row); });
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
    validateRowStock(row);
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('.item-row').remove();
        calculateTotals();
    } else {
        alert('La facture doit contenir au moins une ligne.');
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
        taxesHtml += `<div class="d-flex justify-content-between mb-1"><span class="text-muted">${name} (${rate}%)</span><span>${formatNumber(taxAmount)} FCFA</span></div>`;
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

    document.getElementById('taxesDisplay').innerHTML   = taxesHtml;
    document.getElementById('totalDisplay').textContent = formatNumber(total) + ' FCFA';

    document.getElementById('subtotal').value        = Math.round(subtotal * 100) / 100;
    document.getElementById('discount_amount').value = Math.round(globalDisc * 100) / 100;
    document.getElementById('tax_amount').value      = Math.round(totalTax * 100) / 100;
    document.getElementById('total_amount').value    = Math.round(total * 100) / 100;
}

function applyPromoCode() {
    const code     = document.getElementById('promoCodeInput').value.trim().toUpperCase();
    const amount   = parseFloat(document.getElementById('subtotal').value) || 0;
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

// Product card click to add to invoice
document.querySelectorAll('.product-selectable').forEach(function(card) {
    card.addEventListener('click', function() {
        const parent = this.closest('.product-card-item');

        // Block out-of-stock products
        const trackInv = parent.dataset.track === '1';
        const stock    = parseFloat(parent.dataset.stock) || 0;
        if (trackInv && stock <= 0) {
            return; // rupture de stock, on n'ajoute pas
        }

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

// ---- Stock validation ----
function validateRowStock(row) {
    const productId = row.querySelector('.item-product-id')?.value;
    const type      = row.querySelector('.item-type')?.value;
    const qty       = parseFloat(row.querySelector('.item-quantity')?.value) || 0;
    const alertEl   = row.querySelector('.item-stock-alert');
    const msgEl     = row.querySelector('.item-stock-alert-msg');

    if (type !== 'product' || !productId) {
        row.classList.remove('border-danger');
        if (alertEl) alertEl.classList.add('d-none');
        return true;
    }

    const stockInfo = productStockMap[productId];
    if (!stockInfo || !stockInfo.track_inventory) {
        row.classList.remove('border-danger');
        if (alertEl) alertEl.classList.add('d-none');
        return true;
    }

    const stockQty = stockInfo.stock_quantity;
    const stockInfoEl = row.querySelector('.item-stock-info');
    if (stockInfoEl) stockInfoEl.textContent = 'Stock : ' + stockQty;

    if (qty > stockQty) {
        row.classList.add('border-danger');
        if (alertEl) alertEl.classList.remove('d-none');
        if (msgEl) {
            msgEl.textContent = stockQty <= 0
                ? 'Produit en rupture de stock.'
                : `Qté demandée (${qty}) > stock disponible (${stockQty}).`;
        }
        return false;
    }

    row.classList.remove('border-danger');
    if (alertEl) alertEl.classList.add('d-none');
    return true;
}

function validateAllStock() {
    let valid = true;
    document.querySelectorAll('.item-row').forEach(row => {
        if (!validateRowStock(row)) valid = false;
    });
    return valid;
}

// Bloquer la soumission si stock insuffisant
document.getElementById('invoiceForm')?.addEventListener('submit', function(e) {
    if (!validateAllStock()) {
        e.preventDefault();
        const globalAlert = document.getElementById('stockGlobalAlert');
        if (globalAlert) {
            globalAlert.classList.remove('d-none');
            globalAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        const firstError = document.querySelector('.item-row.border-danger');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Paiement / Livraison immédiats
document.getElementById('payment_immediate')?.addEventListener('change', function() {
    document.getElementById('paymentOptions').style.display = this.checked ? '' : 'none';
});

function togglePaymentRef() {
    const needsRef = ['check', 'bank_transfer', 'mobile_money']
        .includes(document.getElementById('payment_method')?.value);
    document.getElementById('paymentRefDiv').style.display = needsRef ? '' : 'none';
}
</script>
@endpush
