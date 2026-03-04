@extends('layouts.admin')

@section('title', 'Facture ' . $invoice->invoice_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Factures</a></li>
<li class="breadcrumb-item active">{{ $invoice->invoice_number }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Invoice Header --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ $invoice->invoice_number }}</h3>
                        <p class="text-muted mb-0">
                            Facturée le {{ $invoice->invoice_date->format('d/m/Y') }}
                            @if($invoice->quote_id)
                                • Issue du devis <a href="{{ route('quotes.show', $invoice->quote_id) }}">{{ $invoice->quote->quote_number }}</a>
                            @endif
                        </p>
                    </div>
                    <span class="badge bg-{{ $invoice->status_color }} fs-6">
                        {{ $invoice->status_label }}
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Client & Invoice Info --}}
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Client</h6>
                    </div>
                    <div class="card-body">
                        <p class="fw-semibold mb-1">{{ $invoice->client_name }}</p>
                        @if($invoice->client_email)
                            <p class="mb-1"><i class="fas fa-envelope me-2 text-muted"></i>{{ $invoice->client_email }}</p>
                        @endif
                        @if($invoice->client_phone)
                            <p class="mb-1"><i class="fas fa-phone me-2 text-muted"></i>{{ $invoice->client_phone }}</p>
                        @endif
                        @if($invoice->client_address)
                            <p class="mb-0 text-muted">{{ $invoice->client_address }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent">
                        <h6 class="card-title mb-0"><i class="fas fa-calendar me-2 text-primary"></i>Dates</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Date facturation</span>
                            <span class="fw-semibold">{{ $invoice->invoice_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Échéance</span>
                            <span class="fw-semibold {{ $invoice->is_overdue ? 'text-danger' : '' }}">
                                {{ $invoice->due_date->format('d/m/Y') }}
                                @if($invoice->is_overdue)
                                    <i class="fas fa-exclamation-triangle ms-1"></i>
                                @endif
                            </span>
                        </div>
                        @if($invoice->is_overdue)
                            <div class="alert alert-danger mb-0 py-2">
                                <small><i class="fas fa-clock me-1"></i>En retard de {{ $invoice->due_date->diffInDays(now()) }} jour(s)</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Items Table --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Articles facturés</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-center">Type</th>
                                <th class="text-center">Qté</th>
                                <th class="text-end">P.U.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $item->type === 'service' ? 'info' : 'warning' }}">
                                        {{ $item->type === 'service' ? 'Service' : 'Produit' }}
                                    </span>
                                </td>
                                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-end">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($item->total, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="5" class="text-end">Sous-total HT</td>
                                <td class="text-end fw-semibold">{{ number_format($invoice->subtotal, 0, ',', ' ') }}</td>
                            </tr>
                            @foreach($invoice->taxes as $tax)
                            <tr>
                                <td colspan="5" class="text-end">{{ $tax->tax_name }} ({{ $tax->tax_rate }}%)</td>
                                <td class="text-end">{{ number_format($tax->tax_amount, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                            <tr class="fw-bold fs-5">
                                <td colspan="5" class="text-end">Total TTC</td>
                                <td class="text-end text-primary">{{ number_format($invoice->total_amount, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Delivery History --}}
        @can('deliveries.view')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="fas fa-truck me-2 text-primary"></i>Historique des livraisons</h6>
                @can('deliveries.create')
                @if($invoice->isFullyDelivered())
                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Entièrement livré</span>
                @else
                <form action="{{ route('deliveries.createFromInvoice', $invoice) }}" method="POST"
                      onsubmit="return confirm('Créer un nouveau bon de livraison pour les quantités restantes ?')">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-plus me-1"></i>Nouveau BL
                    </button>
                </form>
                @endif
                @endcan
            </div>
            <div class="card-body p-0">
                @if($invoice->deliveryNotes->count() === 0)
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-truck fa-2x mb-2 d-block"></i>
                        <small>Aucun bon de livraison pour cette facture</small>
                    </div>
                @else
                @php
                    // Calcul global des quantités commandées et livrées
                    $orderedQtys = [];
                    foreach ($invoice->items as $invItem) {
                        $key = $invItem->product_id ? "p_{$invItem->product_id}" : 'd_' . md5(trim($invItem->description));
                        $orderedQtys[$key] = ['description' => $invItem->description, 'qty' => (float) $invItem->quantity];
                    }
                    $totalDeliveredQtys = [];
                    foreach ($invoice->deliveryNotes->where('status', 'delivered') as $dn) {
                        foreach ($dn->items as $dnItem) {
                            $key = $dnItem->product_id ? "p_{$dnItem->product_id}" : 'd_' . md5(trim($dnItem->description));
                            $totalDeliveredQtys[$key] = ($totalDeliveredQtys[$key] ?? 0) + (float) $dnItem->quantity;
                        }
                    }
                    $fullyDelivered = !empty($orderedQtys) && collect($orderedQtys)->every(function ($item, $key) use ($totalDeliveredQtys) {
                        return ($totalDeliveredQtys[$key] ?? 0) >= $item['qty'];
                    });
                @endphp

                {{-- Progression globale --}}
                <div class="p-3 border-bottom">
                    <div class="d-flex justify-content-between mb-1">
                        <small class="text-muted">Progression des livraisons</small>
                        @if($fullyDelivered)
                            <span class="badge bg-success">Entièrement livré</span>
                        @else
                            <span class="badge bg-warning text-dark">En cours</span>
                        @endif
                    </div>
                    @foreach($orderedQtys as $key => $info)
                    @php $deliveredQty = $totalDeliveredQtys[$key] ?? 0; $pct = $info['qty'] > 0 ? min(100, round($deliveredQty / $info['qty'] * 100)) : 0; @endphp
                    <div class="mb-1">
                        <div class="d-flex justify-content-between mb-0" style="font-size:.8rem;">
                            <span>{{ Str::limit($info['description'], 40) }}</span>
                            <span>{{ number_format($deliveredQty, 2) }} / {{ number_format($info['qty'], 2) }}</span>
                        </div>
                        <div class="progress" style="height:4px;">
                            <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : 'info' }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Liste des BL --}}
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° BL</th>
                                <th>Date prévue</th>
                                <th>Livreur</th>
                                <th class="text-center">Statut</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->deliveryNotes as $dn)
                            <tr>
                                <td>
                                    <a href="{{ route('deliveries.show', $dn) }}" class="fw-semibold text-decoration-none small">
                                        {{ $dn->delivery_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $dn->planned_date->format('d/m/Y') }}</td>
                                <td class="small">{{ $dn->livreur ?? '—' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $dn->status_color }}">{{ $dn->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('deliveries.pdf', $dn) }}" class="btn btn-xs btn-outline-secondary btn-sm" target="_blank" title="PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endcan

        {{-- Returns History --}}
        @can('returns.view')
        @if($invoice->deliveryReturns->count() > 0 || $invoice->isFullyDelivered())
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="fas fa-undo me-2 text-warning"></i>Retours clients</h6>
                @if($invoice->isFullyDelivered())
                @can('returns.create')
                {{-- Find a delivered BL to link the return to --}}
                @php $lastDeliveredBL = $invoice->deliveryNotes->where('status', 'delivered')->last(); @endphp
                @if($lastDeliveredBL)
                <a href="{{ route('returns.create', ['delivery_note_id' => $lastDeliveredBL->id]) }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-plus me-1"></i>Nouveau retour
                </a>
                @endif
                @endcan
                @endif
            </div>
            <div class="card-body p-0">
                @if($invoice->deliveryReturns->count() === 0)
                    <div class="text-center py-3 text-muted small">
                        <i class="fas fa-undo me-1"></i>Aucun retour enregistré
                    </div>
                @else
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>N° Retour</th>
                                <th>Date</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Résolution</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->deliveryReturns as $ret)
                            <tr>
                                <td>
                                    <a href="{{ route('returns.show', $ret) }}" class="fw-semibold small">
                                        {{ $ret->return_number }}
                                    </a>
                                </td>
                                <td class="small">{{ $ret->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $ret->status_color }}">{{ $ret->status_label }}</span>
                                </td>
                                <td class="text-center">
                                    @if($ret->resolution)
                                        <span class="badge bg-light text-dark border small">{{ $ret->resolution_label }}</span>
                                    @else —
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('returns.show', $ret) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endcan

        {{-- Payments History --}}
        @feature('payments_tracking')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Historique des paiements</h6>
                @if($invoice->balance > 0 && $invoice->status !== 'cancelled')
                    @can('payments.create')
                    <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i>Enregistrer un paiement
                    </a>
                    @endcan
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Méthode</th>
                                <th>Référence</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark">{{ $payment->method_label }}</span></td>
                                <td>{{ $payment->reference ?? '-' }}</td>
                                <td class="text-end fw-semibold text-success">
                                    +{{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Aucun paiement enregistré
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($invoice->payments->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end">Total payé</td>
                                <td class="text-end fw-semibold text-success">{{ number_format($invoice->paid_amount, 0, ',', ' ') }} FCFA</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end">Reste à payer</td>
                                <td class="text-end fw-semibold {{ $invoice->balance > 0 ? 'text-warning' : 'text-success' }}">
                                    {{ number_format($invoice->balance, 0, ',', ' ') }} FCFA
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        @endfeature
    </div>
    
    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Amount Summary --}}
        <div class="card border-0 shadow-sm mb-4 {{ $invoice->payment_status === 'paid' ? 'bg-success text-white' : '' }}">
            <div class="card-body text-center">
                <p class="mb-1 {{ $invoice->payment_status === 'paid' ? '' : 'text-muted' }}">Montant total</p>
                <h2 class="mb-1">{{ number_format($invoice->total_amount, 0, ',', ' ') }}</h2>
                <p class="mb-0 {{ $invoice->payment_status === 'paid' ? '' : 'text-muted' }}">FCFA</p>
                
                @if($invoice->balance > 0 && $invoice->balance < $invoice->total_amount)
                    <hr class="{{ $invoice->payment_status === 'paid' ? 'border-light' : '' }}">
                    <div class="d-flex justify-content-between">
                        <span>Payé</span>
                        <span class="fw-semibold">{{ number_format($invoice->paid_amount, 0, ',', ' ') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Reste</span>
                        <span class="fw-semibold">{{ number_format($invoice->balance, 0, ',', ' ') }}</span>
                    </div>
                @endif
                
                @if($invoice->payment_status === 'paid')
                    <div class="mt-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <p class="mb-0 mt-2">Entièrement payée</p>
                    </div>
                @endif
            </div>
        </div>
        
        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('invoices.pdf', $invoice) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Télécharger PDF
                    </a>
                    
                    @if($invoice->client_email)
                    <button type="button" class="btn btn-outline-primary" onclick="sendByEmail()">
                        <i class="fas fa-envelope me-2"></i>Envoyer par email
                    </button>
                    @endif
                    
                    @if($invoice->balance > 0 && $invoice->status !== 'cancelled')
                        @can('payments.create')
                        <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" class="btn btn-success">
                            <i class="fas fa-money-bill me-2"></i>Enregistrer paiement
                        </a>
                        @endcan
                    @endif
                    
                    @if($invoice->status === 'draft')
                        @can('invoices.edit')
                        <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        @endcan
                    @endif

                    @can('invoices.delete')
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Supprimer cette facture ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
        
        {{-- Status Update --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Changer le statut</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Statut actuel : <span class="badge bg-{{ $invoice->status_color }}">{{ $invoice->status_label }}</span>
                </p>
                <form action="{{ route('invoices.updateStatus', $invoice) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="draft"     {{ $invoice->status === 'draft'                      ? 'selected' : '' }}>📝 Brouillon</option>
                            <option value="sent"      {{ $invoice->status === 'sent'                       ? 'selected' : '' }}>📤 Envoyée</option>
                            <option value="paid"      {{ $invoice->payment_status === 'paid'               ? 'selected' : '' }}>✅ Payée</option>
                            <option value="partial"   {{ $invoice->payment_status === 'partial'            ? 'selected' : '' }}>💰 Paiement partiel</option>
                            <option value="cancelled" {{ $invoice->status === 'cancelled'                  ? 'selected' : '' }}>❌ Annulée</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-sync-alt me-1"></i>Mettre à jour le statut
                    </button>
                </form>
            </div>
        </div>

        {{-- Notes --}}
        @if($invoice->notes)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
            </div>
            <div class="card-body">
                <p class="mb-0 text-muted">{!! nl2br(e($invoice->notes)) !!}</p>
            </div>
        </div>
        @endif

        {{-- Code de livraison (PIN) --}}
        @if($invoice->status !== 'cancelled')
        @can('invoices.edit')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="fas fa-key me-2 text-warning"></i>Code de livraison</h6>
            </div>
            <div class="card-body">
                {{-- Flash : PIN affiché une seule fois après génération --}}
                @if(session('pin_generated'))
                <div class="alert alert-success alert-dismissible mb-3 py-2">
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
                    <div class="small text-muted mb-1">Code généré — notez-le :</div>
                    <div class="fs-4 fw-bold letter-spacing-lg text-center" style="letter-spacing:.3em; font-family:monospace;">
                        {{ session('pin_generated') }}
                    </div>
                    <div class="small text-muted mt-1">Visible sur le PDF de la facture.</div>
                </div>
                @endif

                @if($invoice->hasDeliveryPin())
                    <div class="text-center mb-2">
                        <div class="fs-5 fw-bold text-muted" style="letter-spacing:.25em; font-family:monospace;">
                            &#x25CF;&#x25CF;&#x25CF;&#x25CF;&#x25CF;&#x25CF;&#x25CF;&#x25CF;
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            Généré le {{ $invoice->delivery_pin_generated_at?->format('d/m/Y') }}
                        </div>
                        <div class="small text-muted">Affiché sur le PDF de la facture.</div>
                    </div>
                    <form action="{{ route('invoices.generatePin', $invoice) }}" method="POST"
                          onsubmit="return confirm('Regénérer un nouveau code ? L\'ancien ne sera plus valide.')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-warning w-100">
                            <i class="fas fa-sync-alt me-1"></i>Regénérer le code
                        </button>
                    </form>
                @else
                    <p class="text-muted small mb-3">Générez un code confidentiel à communiquer au client. Ce code permettra de confirmer la livraison sans signature.</p>
                    <form action="{{ route('invoices.generatePin', $invoice) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-key me-2"></i>Générer le code
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endcan
        @endif
    </div>
</div>
@endsection
