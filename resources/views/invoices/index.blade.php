@extends('layouts.admin')

@section('title', 'Factures')

@section('breadcrumb')
<li class="breadcrumb-item active">Factures</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Factures</h1>
        <p class="text-muted mb-0">{{ $invoices->total() }} facture(s) au total</p>
    </div>
    <div class="d-flex gap-2">
        @role('company_admin')
        <a href="{{ route('invoices.trash') }}" class="btn btn-outline-danger">
            <i class="fas fa-trash-alt me-1"></i>Corbeille
        </a>
        @endrole
        @can('invoices.create')
        <a href="{{ route('invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle facture
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Total facturé</p>
                <h4 class="mb-0">{{ number_format($stats['total'] ?? 0, 0, ',', ' ') }} <small class="text-muted">FCFA</small></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Payées</p>
                <h4 class="mb-0 text-success">{{ number_format($stats['paid'] ?? 0, 0, ',', ' ') }} <small class="text-muted">FCFA</small></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <p class="text-muted mb-1">En attente</p>
                <h4 class="mb-0 text-warning">{{ number_format($stats['pending'] ?? 0, 0, ',', ' ') }} <small class="text-muted">FCFA</small></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-danger border-4">
            <div class="card-body">
                <p class="text-muted mb-1">En retard</p>
                <h4 class="mb-0 text-danger">{{ number_format($stats['overdue'] ?? 0, 0, ',', ' ') }} <small class="text-muted">FCFA</small></h4>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('invoices.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="N° facture, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyée</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Payée</option>
                    <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partielle</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>En retard</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" placeholder="Du">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" placeholder="Au">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Invoices Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <x-sortable-th column="invoice_number" label="N° Facture" />
                        <x-sortable-th column="client_name" label="Client" />
                        <x-sortable-th column="invoice_date" label="Date" />
                        <x-sortable-th column="due_date" label="Échéance" />
                        <x-sortable-th column="total_amount" label="Montant" class="text-end" />
                        <x-sortable-th column="status" label="Statut" class="text-center" />
                        <th class="text-center">Livraison</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('invoices.show', $invoice) }}" class="fw-semibold text-decoration-none">
                                {{ $invoice->invoice_number }}
                            </a>
                            @if($invoice->quote_id)
                                <br><small class="text-muted">Devis: {{ $invoice->quote->quote_number ?? '-' }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ Str::limit($invoice->client_name, 30) }}</span>
                            @if($invoice->client_id)
                                <br><a href="{{ route('clients.show', $invoice->client_id) }}" class="small text-decoration-none">Voir fiche</a>
                            @endif
                        </td>
                        <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="{{ $invoice->is_overdue ? 'text-danger fw-semibold' : '' }}">
                                {{ $invoice->due_date->format('d/m/Y') }}
                            </span>
                            @if($invoice->is_overdue)
                                <br><small class="text-danger">{{ $invoice->due_date->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-semibold">{{ number_format($invoice->total_amount, 0, ',', ' ') }}</span>
                            @if($invoice->paid_amount > 0 && $invoice->paid_amount < $invoice->total_amount)
                                <br><small class="text-success">Payé: {{ number_format($invoice->paid_amount, 0, ',', ' ') }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $invoice->status_color }}">
                                {{ $invoice->status_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            @php $dp = $invoice->delivery_progress; @endphp
                            @if($dp['total'] === 0)
                                <span class="text-muted small">—</span>
                            @elseif($dp['fully_delivered'])
                                <span class="badge bg-success" title="{{ $dp['total'] }} BL livré(s)">
                                    <i class="fas fa-check me-1"></i>Livré
                                </span>
                            @else
                                <span class="badge bg-warning text-dark" title="{{ $dp['delivered'] }}/{{ $dp['total'] }} livré(s)">
                                    {{ $dp['delivered'] }}/{{ $dp['total'] }} BL
                                </span>
                                @if($dp['in_transit'] > 0)
                                    <br><small class="text-info">🚚 en transit</small>
                                @endif
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle table-dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('invoices.show', $invoice) }}">
                                            <i class="fas fa-eye me-2 text-muted"></i>Voir
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('invoices.pdf', $invoice) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>PDF sans signature
                                        </a>
                                    </li>
                                    @if($invoice->company->signature_image)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('invoices.pdf', [$invoice, 'signature' => 1]) }}" target="_blank">
                                            <i class="fas fa-signature me-2 text-primary"></i>PDF avec signature
                                        </a>
                                    </li>
                                    @endif
                                    @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                        @can('payments.create')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}">
                                                <i class="fas fa-money-bill me-2 text-success"></i>Enregistrer paiement
                                            </a>
                                        </li>
                                        @endcan
                                        @can('invoices.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('invoices.edit', $invoice) }}">
                                                <i class="fas fa-edit me-2 text-muted"></i>Modifier
                                            </a>
                                        </li>
                                        @endcan
                                    @endif
                                    @can('invoices.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" onsubmit="return confirm('Supprimer cette facture ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucune facture trouvée</p>
                            @can('invoices.create')
                            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer la première facture
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($invoices->hasPages())
    <div class="card-footer bg-transparent">
        {{ $invoices->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table-dropdown-toggle').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            }
        });
    });
});
</script>
@endpush
