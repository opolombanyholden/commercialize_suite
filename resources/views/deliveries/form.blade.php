{{--
    Formulaire partagé create/edit bon de livraison
    Variables attendues : $delivery (edit only), $clients, $products, $sites, $selectedClient, $fromInvoice
--}}
@php
    $isEdit      = isset($delivery);
    $formAction  = $isEdit ? route('deliveries.update', $delivery) : route('deliveries.store');
    $formMethod  = $isEdit ? 'PUT' : 'POST';

    $old = fn(string $key, $default = null) => old($key, $isEdit ? data_get($delivery, $key) : $default);
@endphp

<form action="{{ $formAction }}" method="POST" id="delivery-form">
    @csrf
    @method($formMethod)

    <div class="row g-4">
        {{-- Left column --}}
        <div class="col-lg-8">

            {{-- Client --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Client</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client (optionnel)</label>
                            <select class="form-select" name="client_id" id="client_id">
                                <option value="">— Saisie manuelle —</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}"
                                        data-name="{{ $client->display_name }}"
                                        data-email="{{ $client->email }}"
                                        data-phone="{{ $client->phone }}"
                                        data-address="{{ $client->full_address }}"
                                        {{ $old('client_id', $selectedClient?->id) == $client->id ? 'selected' : '' }}>
                                        {{ $client->display_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom du client <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                name="client_name" id="client_name"
                                value="{{ $old('client_name', $fromInvoice?->client_name) }}" required>
                            @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="client_email" id="client_email"
                                value="{{ $old('client_email', $fromInvoice?->client_email) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control" name="client_phone" id="client_phone"
                                value="{{ $old('client_phone', $fromInvoice?->client_phone) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse du client</label>
                            <textarea class="form-control" name="client_address" id="client_address" rows="2">{{ $old('client_address', $fromInvoice?->client_address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse de livraison <small class="text-muted">(si différente)</small></label>
                            <textarea class="form-control" name="delivery_address" rows="2">{{ $old('delivery_address', $isEdit ? $delivery->delivery_address : $fromInvoice?->client_address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Articles --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Articles à livrer</h6>
                    <div class="d-flex gap-2">
                        @if(user_has_version('standard'))
                        <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#catalogModal">
                            <i class="fas fa-search me-1"></i>Depuis le catalogue
                        </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                            <i class="fas fa-plus me-1"></i>Ajouter manuellement
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center" style="width:100px;">Quantité</th>
                                    <th style="width:100px;">Unité</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Pré-rempli en édition ou depuis facture --}}
                                @php
                                    $existingItems = [];
                                    if ($isEdit) {
                                        foreach ($delivery->items->values() as $idx => $item) {
                                            $arr = $item->toArray();
                                            $arr['max_qty'] = isset($itemMaxQtys) ? ($itemMaxQtys[$idx] ?? null) : null;
                                            $existingItems[] = $arr;
                                        }
                                    } elseif ($fromInvoice) {
                                        $existingItems = $fromInvoice->items->map(fn($i) => [
                                            'product_id'  => $i->product_id,
                                            'description' => $i->description,
                                            'quantity'    => $i->quantity,
                                            'unit'        => null,
                                            'max_qty'     => null,
                                        ])->toArray();
                                    }
                                @endphp
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top" id="emptyItems" style="{{ count($existingItems) > 0 ? 'display:none' : '' }}">
                        <p class="text-muted text-center mb-0 small">Aucun article — cliquez sur "Ajouter manuellement"</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-lg-4">

            {{-- Infos livraison --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-truck me-2 text-primary"></i>Livraison</h6>
                </div>
                <div class="card-body">
                    @if($fromInvoice)
                    <input type="hidden" name="invoice_id" value="{{ $fromInvoice->id }}">
                    <div class="alert alert-info py-2 mb-3">
                        <small><i class="fas fa-link me-1"></i>Lié à la facture <strong>{{ $fromInvoice->invoice_number }}</strong></small>
                    </div>
                    @elseif($isEdit && $delivery->invoice_id)
                    <input type="hidden" name="invoice_id" value="{{ $delivery->invoice_id }}">
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Date de livraison prévue <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('planned_date') is-invalid @enderror"
                            name="planned_date"
                            value="{{ $old('planned_date', $isEdit ? $delivery->planned_date?->format('Y-m-d') : now()->addDay()->format('Y-m-d')) }}"
                            required>
                        @error('planned_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Livreur</label>
                        <input type="text" class="form-control" name="livreur"
                            value="{{ $old('livreur', $isEdit ? $delivery->livreur : '') }}"
                            placeholder="Nom du livreur">
                    </div>

                    @if($sites->count() > 1)
                    <div class="mb-3">
                        <label class="form-label">Site</label>
                        <select class="form-select" name="site_id">
                            <option value="">— Aucun —</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}"
                                    {{ $old('site_id', $isEdit ? $delivery->site_id : null) == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-0">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Instructions particulières...">{{ $old('notes', $isEdit ? $delivery->notes : '') }}</textarea>
                    </div>
                </div>
            </div>

            @if(!$isEdit)
            {{-- Livraison immédiate --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="delivery_immediate" name="delivery_immediate" value="1"
                            {{ old('delivery_immediate', 1) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="delivery_immediate">
                            <i class="fas fa-check-circle me-1 text-success"></i>Livraison effectuée
                        </label>
                    </div>
                    <small class="text-muted d-block mt-1">
                        Si activé, le bon sera marqué comme <strong>livré</strong> et le stock sera décrémenté automatiquement.
                    </small>
                </div>
            </div>
            @endif

            {{-- Submit --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary" onclick="return confirmSubmit()">
                    <i class="fas fa-save me-2"></i>{{ $isEdit ? 'Mettre à jour' : 'Créer le bon de livraison' }}
                </button>
                <a href="{{ $isEdit ? route('deliveries.show', $delivery) : route('deliveries.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</form>

{{-- Catalogue modal (version standard+) --}}
@if(user_has_version('standard'))
<div class="modal fade" id="catalogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-search me-2"></i>Sélectionner depuis le catalogue</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control mb-3" id="catalogSearch" placeholder="Rechercher un produit...">
                <div class="row g-3" id="catalogGrid">
                    @forelse($products as $product)
                    @php
                        $outOfStock = $product->type === 'product' && $product->track_inventory && ($product->stock_quantity ?? 0) <= 0;
                    @endphp
                    <div class="col-md-4 catalog-item"
                         data-name="{{ strtolower($product->name) }}"
                         data-stock="{{ $product->stock_quantity ?? 0 }}"
                         data-track="{{ $product->track_inventory ? '1' : '0' }}">
                        <div class="card h-100 product-card border{{ $outOfStock ? ' opacity-50' : '' }}"
                            data-id="{{ $product->id }}"
                            data-name="{{ $product->name }}"
                            data-unit="{{ $product->unit ?? '' }}"
                            style="{{ $outOfStock ? 'cursor:not-allowed;' : 'cursor:pointer;' }}">
                            <div class="card-body p-3">
                                <p class="fw-semibold mb-1 small">{{ $product->name }}</p>
                                <p class="text-muted mb-0" style="font-size:0.8rem;">{{ Str::limit($product->description, 50) }}</p>
                                @if($product->track_inventory)
                                    @if($outOfStock)
                                        <span class="badge bg-danger mt-1"><i class="fas fa-times-circle me-1"></i>Rupture de stock</span>
                                    @else
                                        <small class="text-muted d-block mt-1"><i class="fas fa-warehouse me-1"></i>Stock : {{ $product->stock_quantity }}</small>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 text-muted">
                        <i class="fas fa-box-open fa-2x mb-2 d-block"></i>
                        <p class="mb-0">Aucun produit disponible dans le catalogue.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Item row template --}}
<template id="itemRowTemplate">
    <tr class="item-row">
        <td>
            <input type="hidden" name="items[__INDEX__][product_id]" class="item-product-id">
            <input type="text" class="form-control form-control-sm" name="items[__INDEX__][description]"
                placeholder="Description de l'article" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-center" name="items[__INDEX__][quantity]"
                value="1" min="0.01" step="0.01" required style="width:80px;">
        </td>
        <td>
            <input type="text" class="form-control form-control-sm" name="items[__INDEX__][unit]"
                placeholder="pcs / kg…" style="width:80px;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
(function () {
    let itemIndex = 0;
    const body      = document.getElementById('itemsBody');
    const empty     = document.getElementById('emptyItems');
    const template  = document.getElementById('itemRowTemplate');

    function refreshEmpty() {
        empty.style.display = body.querySelectorAll('.item-row').length === 0 ? '' : 'none';
    }

    function addItem(productId = '', name = '', unit = '', qty = 1, maxQty = null) {
        const html = template.innerHTML.replaceAll('__INDEX__', itemIndex++);
        const tmp  = document.createElement('tbody');
        tmp.innerHTML = html;
        const row = tmp.querySelector('tr');
        if (productId) row.querySelector('.item-product-id').value = productId;
        if (name)      row.querySelector('input[name$="[description]"]').value = name;
        if (unit)      row.querySelector('input[name$="[unit]"]').value = unit;
        const qtyInput = row.querySelector('input[name$="[quantity]"]');
        qtyInput.value = qty;
        if (maxQty !== null && maxQty !== undefined) {
            qtyInput.setAttribute('max', maxQty);
            qtyInput.title = 'Maximum livrable : ' + maxQty;
            const hint = document.createElement('small');
            hint.className = 'text-muted d-block text-center mt-1';
            hint.textContent = 'max : ' + maxQty;
            qtyInput.parentNode.appendChild(hint);
        }
        row.querySelector('.remove-item').addEventListener('click', function () {
            row.remove();
            refreshEmpty();
        });
        body.appendChild(row);
        refreshEmpty();
    }

    // Pré-remplir les lignes existantes
    @foreach($existingItems as $item)
    addItem(
        '{{ $item["product_id"] ?? "" }}',
        @json($item['description'] ?? ''),
        '{{ $item["unit"] ?? "" }}',
        {{ $item["quantity"] ?? 1 }},
        {{ isset($item["max_qty"]) && $item["max_qty"] !== null ? $item["max_qty"] : 'null' }}
    );
    @endforeach

    document.getElementById('addItemBtn').addEventListener('click', () => addItem());

    // Confirmation avant soumission si livraison immédiate
    window.confirmSubmit = function () {
        const cb = document.getElementById('delivery_immediate');
        if (cb && cb.checked) {
            return confirm('La livraison sera marquée comme effectuée et le stock sera décrémenté. Confirmer ?');
        }
        return true;
    };

    // Auto-remplissage client
    document.getElementById('client_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (opt.value) {
            document.getElementById('client_name').value    = opt.dataset.name    || '';
            document.getElementById('client_email').value   = opt.dataset.email   || '';
            document.getElementById('client_phone').value   = opt.dataset.phone   || '';
            document.getElementById('client_address').value = opt.dataset.address || '';
        }
    });

    @if(user_has_version('standard'))
    // Catalogue
    document.getElementById('catalogSearch').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.catalog-item').forEach(el => {
            el.style.display = el.dataset.name.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function () {
            // Bloquer les produits en rupture de stock
            const parent = this.closest('.catalog-item');
            const trackInv = parent.dataset.track === '1';
            const stock = parseFloat(parent.dataset.stock) || 0;
            if (trackInv && stock <= 0) {
                return;
            }

            addItem(this.dataset.id, this.dataset.name, this.dataset.unit, 1);
            bootstrap.Modal.getInstance(document.getElementById('catalogModal')).hide();
        });
    });
    @endif
})();
</script>
@endpush
