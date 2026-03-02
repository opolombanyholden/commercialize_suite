@extends('layouts.admin')

@section('title', $title ?? (isset($document) ? 'Modifier' : 'Nouveau document'))

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route($routePrefix . '.index') }}">{{ $documentType === 'invoice' ? 'Factures' : 'Devis' }}</a></li>
        <li class="breadcrumb-item active">{{ isset($document) ? 'Modifier' : 'Nouveau' }}</li>
    </ol>
</nav>
@endsection

@section('content')
<form action="{{ isset($document) ? route($routePrefix . '.update', $document) : route($routePrefix . '.store') }}" method="POST" id="documentForm">
    @csrf
    @if(isset($document))
        @method('PUT')
    @endif

    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">
                    <i class="fas {{ $documentType === 'invoice' ? 'fa-file-invoice-dollar' : 'fa-file-alt' }} me-2"></i>
                    {{ $title ?? (isset($document) ? 'Modifier' : 'Nouveau') }}
                </h1>
            </div>
            <div class="col-auto">
                <button type="submit" name="action" value="save" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer
                </button>
                <button type="submit" name="action" value="save_pdf" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-2"></i>Enregistrer & PDF
                </button>
                <a href="{{ route($routePrefix . '.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Client Selection -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Informations client</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                        <i class="fas fa-search me-1"></i>Rechercher
                    </button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id', $document->client_id ?? '') }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="client_name" class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror" id="client_name" name="client_name" value="{{ old('client_name', $document->client_name ?? '') }}" required>
                            @error('client_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="client_email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('client_email') is-invalid @enderror" id="client_email" name="client_email" value="{{ old('client_email', $document->client_email ?? '') }}">
                            @error('client_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="client_phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('client_phone') is-invalid @enderror" id="client_phone" name="client_phone" value="{{ old('client_phone', $document->client_phone ?? '') }}">
                            @error('client_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="client_address" class="form-label">Adresse</label>
                            <input type="text" class="form-control @error('client_address') is-invalid @enderror" id="client_address" name="client_address" value="{{ old('client_address', $document->client_address ?? '') }}">
                            @error('client_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Articles</h5>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addItem()">
                        <i class="fas fa-plus me-1"></i>Ajouter
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0" id="itemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Description</th>
                                    <th style="width: 12%;">Type</th>
                                    <th style="width: 12%;">Quantité</th>
                                    <th style="width: 18%;">Prix unitaire</th>
                                    <th style="width: 15%;">Total</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @if(isset($document) && $document->items)
                                    @foreach($document->items as $index => $item)
                                    <tr class="item-row">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                            <input type="text" class="form-control" name="items[{{ $index }}][description]" value="{{ $item->description }}" required>
                                        </td>
                                        <td>
                                            <select class="form-select" name="items[{{ $index }}][type]">
                                                <option value="product" {{ $item->type === 'product' ? 'selected' : '' }}>Produit</option>
                                                <option value="service" {{ $item->type === 'service' ? 'selected' : '' }}>Service</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" class="form-control quantity-input" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="0.001" step="0.001" onchange="calculateRowTotal(this)">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control price-input" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" min="0" onchange="calculateRowTotal(this)">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control row-total" readonly value="{{ number_format($item->total, 0, ',', ' ') }}">
                                            <input type="hidden" name="items[{{ $index }}][total]" class="row-total-hidden" value="{{ $item->total }}">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notes et conditions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $document->notes ?? '') }}</textarea>
                    </div>
                    <div>
                        <label for="terms" class="form-label">Conditions</label>
                        <textarea class="form-control" id="terms" name="terms" rows="3">{{ old('terms', $document->terms ?? 'Paiement à réception de la facture.') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Dates -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dates</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="document_date" class="form-label">Date du document <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('document_date') is-invalid @enderror" id="document_date" name="{{ $documentType }}_date" value="{{ old($documentType . '_date', isset($document) ? $document->{$documentType . '_date'}->format('Y-m-d') : date('Y-m-d')) }}" required>
                    </div>
                    @if($documentType === 'invoice')
                    <div class="mb-3">
                        <label for="due_date" class="form-label">Date d'échéance</label>
                        <input type="date" class="form-control @error('due_date') is-invalid @enderror" id="due_date" name="due_date" value="{{ old('due_date', isset($document) && $document->due_date ? $document->due_date->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}">
                    </div>
                    @else
                    <div class="mb-3">
                        <label for="valid_until" class="form-label">Valide jusqu'au</label>
                        <input type="date" class="form-control @error('valid_until') is-invalid @enderror" id="valid_until" name="valid_until" value="{{ old('valid_until', isset($document) && $document->valid_until ? $document->valid_until->format('Y-m-d') : date('Y-m-d', strtotime('+30 days'))) }}">
                    </div>
                    @endif
                </div>
            </div>

            <!-- Taxes -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Taxes</h5>
                </div>
                <div class="card-body">
                    @foreach($taxes ?? [] as $tax)
                    <div class="form-check mb-2">
                        <input class="form-check-input tax-checkbox" type="checkbox" name="taxes[]" value="{{ $tax->id }}" id="tax-{{ $tax->id }}" 
                            data-rate="{{ $tax->rate }}" data-apply="{{ $tax->apply_to }}"
                            {{ in_array($tax->id, old('taxes', isset($document) ? $document->taxes->pluck('tax_id')->toArray() : [])) ? 'checked' : '' }}
                            onchange="calculateTotals()">
                        <label class="form-check-label" for="tax-{{ $tax->id }}">
                            {{ $tax->name }} ({{ $tax->rate }}%)
                            <small class="text-muted d-block">
                                @if($tax->apply_to === 'products') Sur les produits
                                @elseif($tax->apply_to === 'services') Sur les services
                                @else Sur tout
                                @endif
                            </small>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Totals -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Totaux</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Sous-total HT</span>
                        <strong id="subtotal">0 FCFA</strong>
                        <input type="hidden" name="subtotal" id="subtotal_input" value="0">
                    </div>
                    <div id="taxLines"></div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="h5 mb-0">Total TTC</span>
                        <strong class="h5 mb-0 text-primary" id="total">0 FCFA</strong>
                        <input type="hidden" name="total_amount" id="total_input" value="0">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Client Search Modal -->
<div class="modal fade" id="clientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Rechercher un client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="clientSearch" placeholder="Rechercher par nom, email...">
                <div id="clientResults"></div>
            </div>
        </div>
    </div>
</div>

<!-- Item Template -->
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <input type="hidden" name="items[INDEX][product_id]" value="">
            <input type="text" class="form-control" name="items[INDEX][description]" placeholder="Description" required>
        </td>
        <td>
            <select class="form-select" name="items[INDEX][type]">
                <option value="product">Produit</option>
                <option value="service">Service</option>
            </select>
        </td>
        <td>
            <input type="number" class="form-control quantity-input" name="items[INDEX][quantity]" value="1" min="0.001" step="0.001" onchange="calculateRowTotal(this)">
        </td>
        <td>
            <input type="number" class="form-control price-input" name="items[INDEX][unit_price]" value="0" min="0" onchange="calculateRowTotal(this)">
        </td>
        <td>
            <input type="text" class="form-control row-total" readonly value="0">
            <input type="hidden" name="items[INDEX][total]" class="row-total-hidden" value="0">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem(this)">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let itemIndex = {{ isset($document) ? $document->items->count() : 0 }};

function addItem() {
    const template = document.getElementById('itemRowTemplate');
    const tbody = document.getElementById('itemsBody');
    const clone = template.content.cloneNode(true);
    
    // Replace INDEX placeholder
    clone.querySelectorAll('[name*="INDEX"]').forEach(el => {
        el.name = el.name.replace('INDEX', itemIndex);
    });
    
    tbody.appendChild(clone);
    itemIndex++;
    calculateTotals();
}

function removeItem(btn) {
    btn.closest('tr').remove();
    calculateTotals();
}

function calculateRowTotal(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.row-total').value = formatNumber(total);
    row.querySelector('.row-total-hidden').value = total;
    
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let productTotal = 0;
    let serviceTotal = 0;
    
    document.querySelectorAll('.item-row').forEach(row => {
        const total = parseFloat(row.querySelector('.row-total-hidden').value) || 0;
        const type = row.querySelector('select').value;
        
        subtotal += total;
        if (type === 'product') productTotal += total;
        else serviceTotal += total;
    });
    
    document.getElementById('subtotal').textContent = formatNumber(subtotal) + ' FCFA';
    document.getElementById('subtotal_input').value = subtotal;
    
    // Calculate taxes
    let totalTax = 0;
    const taxLines = document.getElementById('taxLines');
    taxLines.innerHTML = '';
    
    document.querySelectorAll('.tax-checkbox:checked').forEach(checkbox => {
        const rate = parseFloat(checkbox.dataset.rate);
        const applyTo = checkbox.dataset.apply;
        let base = subtotal;
        
        if (applyTo === 'products') base = productTotal;
        else if (applyTo === 'services') base = serviceTotal;
        
        const taxAmount = base * rate / 100;
        totalTax += taxAmount;
        
        const taxLine = document.createElement('div');
        taxLine.className = 'd-flex justify-content-between mb-2 text-muted small';
        taxLine.innerHTML = `<span>${checkbox.nextElementSibling.childNodes[0].textContent.trim()}</span><span>${formatNumber(taxAmount)} FCFA</span>`;
        taxLines.appendChild(taxLine);
    });
    
    const total = subtotal + totalTax;
    document.getElementById('total').textContent = formatNumber(total) + ' FCFA';
    document.getElementById('total_input').value = total;
}

function formatNumber(num) {
    return new Intl.NumberFormat('fr-FR').format(Math.round(num));
}

// Client search
let searchTimeout;
document.getElementById('clientSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetch(`/api/clients/search?q=${this.value}`)
            .then(r => r.json())
            .then(data => {
                const results = document.getElementById('clientResults');
                results.innerHTML = data.map(c => `
                    <div class="p-2 border-bottom client-result" style="cursor:pointer" onclick="selectClient(${JSON.stringify(c).replace(/"/g, '&quot;')})">
                        <strong>${c.name}</strong><br>
                        <small class="text-muted">${c.email || ''} ${c.phone || ''}</small>
                    </div>
                `).join('');
            });
    }, 300);
});

function selectClient(client) {
    document.getElementById('client_id').value = client.id;
    document.getElementById('client_name').value = client.name;
    document.getElementById('client_email').value = client.email || '';
    document.getElementById('client_phone').value = client.phone || '';
    document.getElementById('client_address').value = client.address || '';
    bootstrap.Modal.getInstance(document.getElementById('clientModal')).hide();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.item-row').length === 0) {
        addItem();
    }
    calculateTotals();
});
</script>
@endpush
