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
                        
                        {{-- Client Name --}}
                        <div class="col-md-6">
                            <label for="client_name" class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $invoice->client_name ?? '') }}" required>
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
                    </div>
                </div>
            </div>
            
            {{-- Invoice Items --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>Articles
                    </h5>
                    <div class="d-flex gap-2">
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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Description</th>
                                    <th class="text-center" style="width: 100px;">Type</th>
                                    <th class="text-center" style="width: 80px;">Qté</th>
                                    <th class="text-end" style="width: 120px;">P.U.</th>
                                    <th class="text-end" style="width: 120px;">Total</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Items will be added dynamically --}}
                            </tbody>
                        </table>
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
            
            {{-- Totals --}}
            <div class="card border-0 shadow-sm mb-4 bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total HT</span>
                        <span class="fw-semibold" id="subtotalDisplay">0</span>
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
                    <input type="hidden" name="tax_amount" id="tax_amount" value="0">
                    <input type="hidden" name="total_amount" id="total_amount" value="0">
                </div>
            </div>
            
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
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
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
                <small class="text-muted me-auto"><i class="fas fa-info-circle me-1"></i>Cliquez sur un produit pour l'ajouter à la facture</small>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Item Row Template --}}
<template id="itemTemplate">
    <tr class="item-row">
        <td>
            <input type="text" class="form-control form-control-sm item-description" name="items[INDEX][description]" placeholder="Description de l'article" required>
            <input type="hidden" name="items[INDEX][product_id]" class="item-product-id">
        </td>
        <td>
            <select class="form-select form-select-sm item-type" name="items[INDEX][type]">
                <option value="product">Produit</option>
                <option value="service">Service</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-center item-quantity" name="items[INDEX][quantity]" value="1" min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-end item-price" name="items[INDEX][unit_price]" value="0" min="0" step="1" required>
        </td>
        <td class="text-end">
            <span class="item-total fw-semibold">0</span>
            <input type="hidden" name="items[INDEX][total]" class="item-total-input">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let itemIndex = 0;
const existingItems = @json(old('items', isset($invoice) ? $invoice->items->toArray() : []));

document.addEventListener('DOMContentLoaded', function() {
    // Load existing items
    if (existingItems.length > 0) {
        existingItems.forEach(item => addItem(item));
    } else {
        addItem(); // Add one empty row
    }
    
    // Client select auto-fill
    document.getElementById('client_id').addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        if (option.value) {
            document.getElementById('client_name').value = option.dataset.name || '';
            document.getElementById('client_email').value = option.dataset.email || '';
            document.getElementById('client_phone').value = option.dataset.phone || '';
            document.getElementById('client_address').value = option.dataset.address || '';
        }
    });
    
    // Tax checkboxes
    document.querySelectorAll('.tax-checkbox').forEach(cb => {
        cb.addEventListener('change', calculateTotals);
    });
    
    // Initial calculation
    calculateTotals();
});

function addItem(data = null) {
    const template = document.getElementById('itemTemplate');
    const clone = template.content.cloneNode(true);
    const row = clone.querySelector('tr');
    
    // Replace INDEX with actual index
    row.innerHTML = row.innerHTML.replace(/INDEX/g, itemIndex);
    
    // Fill data if provided
    if (data) {
        row.querySelector('.item-description').value = data.description || '';
        row.querySelector('.item-type').value = data.type || 'product';
        row.querySelector('.item-quantity').value = data.quantity || 1;
        row.querySelector('.item-price').value = data.unit_price || 0;
        if (data.product_id) {
            row.querySelector('.item-product-id').value = data.product_id;
        }
    }
    
    // Add event listeners
    row.querySelector('.item-quantity').addEventListener('input', () => calculateRowTotal(row));
    row.querySelector('.item-price').addEventListener('input', () => calculateRowTotal(row));
    row.querySelector('.item-type').addEventListener('change', calculateTotals);
    
    document.getElementById('itemsBody').appendChild(row);
    itemIndex++;
    
    calculateRowTotal(row);
}

function removeItem(btn) {
    const rows = document.querySelectorAll('.item-row');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculateTotals();
    } else {
        alert('La facture doit contenir au moins une ligne.');
    }
}

function calculateRowTotal(row) {
    const qty = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = qty * price;
    
    row.querySelector('.item-total').textContent = formatNumber(total);
    row.querySelector('.item-total-input').value = total;
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let productTotal = 0;
    let serviceTotal = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const total = parseFloat(row.querySelector('.item-total-input').value) || 0;
        const type = row.querySelector('.item-type').value;
        
        subtotal += total;
        if (type === 'product') {
            productTotal += total;
        } else {
            serviceTotal += total;
        }
    });
    
    // Calculate taxes
    let totalTax = 0;
    let taxesHtml = '';
    
    document.querySelectorAll('.tax-checkbox:checked').forEach(cb => {
        const rate = parseFloat(cb.dataset.rate);
        const name = cb.dataset.name;
        const applyTo = cb.dataset.apply;
        
        let taxBase = subtotal;
        if (applyTo === 'products') taxBase = productTotal;
        if (applyTo === 'services') taxBase = serviceTotal;
        
        const taxAmount = taxBase * rate / 100;
        totalTax += taxAmount;
        
        taxesHtml += `<div class="d-flex justify-content-between mb-1">
            <span class="text-muted">${name} (${rate}%)</span>
            <span>${formatNumber(taxAmount)}</span>
        </div>`;
    });
    
    const total = subtotal + totalTax;
    
    // Update display
    document.getElementById('subtotalDisplay').textContent = formatNumber(subtotal) + ' FCFA';
    document.getElementById('taxesDisplay').innerHTML = taxesHtml;
    document.getElementById('totalDisplay').textContent = formatNumber(total) + ' FCFA';
    
    // Update hidden fields
    document.getElementById('subtotal').value = subtotal;
    document.getElementById('tax_amount').value = totalTax;
    document.getElementById('total_amount').value = total;
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
