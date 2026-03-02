{{--
    Formulaire retour client
    Variables : $deliveryNote (nullable), $invoice, $prefillItems (Collection)
--}}
<form action="{{ route('returns.store') }}" method="POST" id="return-form">
    @csrf

    <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
    @if($deliveryNote)
    <input type="hidden" name="delivery_note_id" value="{{ $deliveryNote->id }}">
    @endif

    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Info facture / BL --}}
            <div class="alert alert-info mb-4">
                <div class="d-flex gap-3">
                    <i class="fas fa-info-circle mt-1"></i>
                    <div>
                        <strong>Facture :</strong>
                        <a href="{{ route('invoices.show', $invoice) }}" class="alert-link">{{ $invoice->invoice_number }}</a>
                        — {{ $invoice->client_name }}
                        @if($deliveryNote)
                            <br><strong>Bon de livraison :</strong>
                            <a href="{{ route('deliveries.show', $deliveryNote) }}" class="alert-link">{{ $deliveryNote->delivery_number }}</a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Articles retournés --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Articles retournés</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addReturnItemBtn">
                        <i class="fas fa-plus me-1"></i>Ajouter un article
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="returnItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center" style="width:110px;">Qté retournée</th>
                                    <th class="text-center" style="width:120px;">Prix unitaire</th>
                                    <th style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody id="returnItemsBody"></tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top text-muted text-center small" id="emptyReturnItems" style="display:none;">
                        Aucun article — cliquez sur "Ajouter un article"
                    </div>
                </div>
            </div>

            {{-- Motif --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h6 class="card-title mb-0"><i class="fas fa-comment me-2 text-primary"></i>Motif du retour</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Raison <span class="text-danger">*</span></label>
                        <select class="form-select" name="reason" required>
                            <option value="">— Sélectionner —</option>
                            <option value="Produit défectueux">Produit défectueux</option>
                            <option value="Produit non conforme">Produit non conforme à la commande</option>
                            <option value="Erreur de livraison">Erreur de livraison</option>
                            <option value="Produit endommagé">Produit endommagé à la livraison</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Notes complémentaires</label>
                        <textarea class="form-control" name="notes" rows="3"
                            placeholder="Détails supplémentaires...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer le retour
                </button>
                <a href="{{ $deliveryNote ? route('deliveries.show', $deliveryNote) : route('invoices.show', $invoice) }}"
                    class="btn btn-outline-secondary">Annuler</a>
            </div>
        </div>
    </div>
</form>

<template id="returnItemRowTemplate">
    <tr class="return-item-row">
        <td>
            <input type="hidden" name="items[__INDEX__][product_id]" class="item-product-id">
            <input type="text" class="form-control form-control-sm" name="items[__INDEX__][description]"
                placeholder="Description" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-center" name="items[__INDEX__][quantity_returned]"
                value="1" min="0.01" step="0.01" required style="width:90px;">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm text-center" name="items[__INDEX__][unit_price]"
                min="0" step="0.01" placeholder="0.00" style="width:100px;">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-return-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
(function () {
    let idx = 0;
    const body     = document.getElementById('returnItemsBody');
    const empty    = document.getElementById('emptyReturnItems');
    const template = document.getElementById('returnItemRowTemplate');

    function refreshEmpty() {
        empty.style.display = body.querySelectorAll('.return-item-row').length === 0 ? '' : 'none';
    }

    function addItem(productId = '', name = '', qty = 1, unitPrice = '') {
        const html = template.innerHTML.replaceAll('__INDEX__', idx++);
        const tmp  = document.createElement('tbody');
        tmp.innerHTML = html;
        const row = tmp.querySelector('tr');
        if (productId) row.querySelector('.item-product-id').value = productId;
        if (name)      row.querySelector('input[name$="[description]"]').value = name;
        row.querySelector('input[name$="[quantity_returned]"]').value = qty;
        if (unitPrice !== '') row.querySelector('input[name$="[unit_price]"]').value = unitPrice;
        row.querySelector('.remove-return-item').addEventListener('click', function () {
            row.remove(); refreshEmpty();
        });
        body.appendChild(row);
        refreshEmpty();
    }

    // Pré-remplir depuis les articles du BL/facture
    @foreach($prefillItems as $item)
    addItem(
        '{{ $item["product_id"] ?? "" }}',
        @json($item['description'] ?? ''),
        {{ $item['quantity'] ?? 1 }},
        '{{ $item["unit_price"] !== null ? number_format((float)$item["unit_price"], 2, ".", "") : "" }}'
    );
    @endforeach

    document.getElementById('addReturnItemBtn').addEventListener('click', () => addItem());
})();
</script>
@endpush
