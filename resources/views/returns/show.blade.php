@extends('layouts.admin')

@section('title', 'Retour ' . $return->return_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Retours clients</a></li>
<li class="breadcrumb-item active">{{ $return->return_number }}</li>
@endsection

@section('content')
<div class="row">
    {{-- Main --}}
    <div class="col-lg-8">

        {{-- Header --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-1">{{ $return->return_number }}</h3>
                        <p class="text-muted mb-0">
                            Créé le {{ $return->created_at->format('d/m/Y') }}
                            &nbsp;•&nbsp; <strong>{{ $return->client_name }}</strong>
                        </p>
                        <div class="mt-1">
                            @if($return->invoice)
                                <a href="{{ route('invoices.show', $return->invoice) }}" class="small">
                                    <i class="fas fa-file-invoice me-1"></i>{{ $return->invoice->invoice_number }}
                                </a>
                            @endif
                            @if($return->deliveryNote)
                                &nbsp;&nbsp;
                                <a href="{{ route('deliveries.show', $return->deliveryNote) }}" class="small">
                                    <i class="fas fa-truck me-1"></i>{{ $return->deliveryNote->delivery_number }}
                                </a>
                            @endif
                        </div>
                    </div>
                    <span class="badge bg-{{ $return->status_color }} fs-6">{{ $return->status_label }}</span>
                </div>
            </div>
        </div>

        {{-- Articles --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>Articles retournés</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th class="text-center">Qté retournée</th>
                                <th class="text-center">Prix unitaire</th>
                                <th class="text-end">Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($return->items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td class="text-center fw-semibold">{{ number_format($item->quantity_returned, 2) }}</td>
                                <td class="text-center text-muted">
                                    {{ $item->unit_price !== null ? number_format($item->unit_price, 2) . ' FCFA' : '—' }}
                                </td>
                                <td class="text-end">
                                    @if($item->unit_price !== null)
                                        {{ number_format($item->line_total, 2) }} FCFA
                                    @else —
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        @php $total = $return->items->sum('line_total'); @endphp
                        @if($total > 0)
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end text-muted fw-semibold">Total avoir potentiel</td>
                                <td class="text-end fw-bold">{{ number_format($total, 2) }} FCFA</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Motif --}}
        @if($return->reason || $return->notes)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-comment me-2"></i>Motif</h6>
            </div>
            <div class="card-body">
                @if($return->reason)
                    <p class="fw-semibold mb-1">{{ $return->reason }}</p>
                @endif
                @if($return->notes)
                    <p class="text-muted mb-0">{!! nl2br(e($return->notes)) !!}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Résolution (si resolved) --}}
        @if($return->isResolved())
        <div class="card border-0 shadow-sm border-start border-success border-3">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0 text-success"><i class="fas fa-check-circle me-2"></i>Résolution</h6>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Type :</strong> {{ $return->resolution_label }}</p>
                @if($return->newDelivery)
                    <a href="{{ route('deliveries.show', $return->newDelivery) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-truck me-1"></i>Voir le nouveau BL {{ $return->newDelivery->delivery_number }}
                    </a>
                @endif
                @if($return->creditNote)
                    <a href="{{ route('invoices.show', $return->creditNote) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-invoice me-1"></i>Voir l'avoir {{ $return->creditNote->invoice_number }}
                    </a>
                @endif
            </div>
        </div>
        @endif

        {{-- Section résolution (si received) --}}
        @if($return->canBeResolved())
        @can('returns.edit')
        <div class="card border-0 shadow-sm border-start border-info border-3">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0 text-info"><i class="fas fa-tools me-2"></i>Résoudre le retour</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">Choisissez comment traiter ce retour :</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-truck fa-2x text-primary mb-3"></i>
                                <h6>Nouvelle livraison</h6>
                                <p class="text-muted small mb-3">Re-livrer les articles retournés au client. Un nouveau bon de livraison sera créé.</p>
                                <form action="{{ route('returns.resolve', $return) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="resolution" value="re_delivery">
                                    <button type="submit" class="btn btn-primary btn-sm"
                                        onclick="return confirm('Créer un nouveau bon de livraison pour ces articles ?')">
                                        <i class="fas fa-truck me-1"></i>Créer un BL
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body text-center p-4">
                                <i class="fas fa-file-invoice-dollar fa-2x text-success mb-3"></i>
                                <h6>Facture d'avoir</h6>
                                @php $total = $return->items->sum('line_total'); @endphp
                                <p class="text-muted small mb-3">
                                    Émettre un avoir pour remboursement.
                                    @if($total > 0)
                                        Montant : <strong>{{ number_format($total, 2) }} FCFA</strong>
                                    @endif
                                </p>
                                <form action="{{ route('returns.resolve', $return) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="resolution" value="credit_note">
                                    <button type="submit" class="btn btn-success btn-sm"
                                        onclick="return confirm('Émettre un avoir pour ce retour ?')">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>Émettre un avoir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan
        @endif

    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">

        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">

                    @if($return->isPending())
                    @can('returns.edit')
                    <form action="{{ route('returns.receive', $return) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-box-open me-2"></i>Marquer comme reçu
                        </button>
                    </form>
                    @endcan
                    @endif

                    @if($return->isPending())
                    @can('returns.delete')
                    <form action="{{ route('returns.destroy', $return) }}" method="POST"
                        onsubmit="return confirm('Supprimer ce retour ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                    @endcan
                    @endif

                    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>Tous les retours
                    </a>
                </div>
            </div>
        </div>

        {{-- Récapitulatif --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Récapitulatif</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Articles</span>
                    <span class="fw-semibold">{{ $return->items->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Qté totale retournée</span>
                    <span class="fw-semibold">{{ number_format($return->items->sum('quantity_returned'), 2) }}</span>
                </div>
                @if($return->items->sum('line_total') > 0)
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Montant total</span>
                    <span class="fw-semibold">{{ number_format($return->items->sum('line_total'), 2) }} FCFA</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
