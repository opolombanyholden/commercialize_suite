@extends('layouts.admin')

@section('title', 'Devis')

@section('breadcrumb')
<li class="breadcrumb-item active">Devis</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Devis</h1>
        <p class="text-muted mb-0">{{ $quotes->total() }} devis au total</p>
    </div>
    @can('quotes.create')
    <a href="{{ route('quotes.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau devis
    </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Total devis</p>
                <h4 class="mb-0">{{ $stats['total'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <p class="text-muted mb-1">En attente</p>
                <h4 class="mb-0 text-warning">{{ $stats['pending_count'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Acceptés</p>
                <h4 class="mb-0 text-success">{{ $stats['accepted_count'] ?? 0 }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Taux conversion</p>
                <h4 class="mb-0 text-info">{{ number_format($stats['conversion_rate'] ?? 0, 1) }}%</h4>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('quotes.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" name="search" placeholder="N° devis, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Envoyé</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepté</option>
                    <option value="declined" {{ request('status') == 'declined' ? 'selected' : '' }}>Refusé</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expiré</option>
                    <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converti</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('quotes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Quotes Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Devis</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Validité</th>
                        <th class="text-end">Montant</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotes as $quote)
                    <tr>
                        <td>
                            <a href="{{ route('quotes.show', $quote) }}" class="fw-semibold text-decoration-none">
                                {{ $quote->quote_number }}
                            </a>
                            @if($quote->converted_to_invoice_id)
                                <br><small class="text-success"><i class="fas fa-check me-1"></i>Facturé</small>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ Str::limit($quote->client_name, 30) }}</span>
                        </td>
                        <td>{{ $quote->quote_date->format('d/m/Y') }}</td>
                        <td>
                            <span class="{{ $quote->is_expired ? 'text-danger' : '' }}">
                                {{ $quote->valid_until->format('d/m/Y') }}
                            </span>
                            @if($quote->is_expired && $quote->status !== 'converted')
                                <br><small class="text-danger">Expiré</small>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ number_format($quote->total_amount, 0, ',', ' ') }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $quote->status_color }}">
                                {{ $quote->status_label }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle table-dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quotes.show', $quote) }}">
                                            <i class="fas fa-eye me-2 text-muted"></i>Voir
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quotes.pdf', $quote) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>Télécharger PDF
                                        </a>
                                    </li>
                                    @if(!$quote->converted_to_invoice_id && $quote->status !== 'rejected')
                                        @can('quotes.convert')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('quotes.convert', $quote) }}" onclick="return confirm('Convertir ce devis en facture ?')">
                                                <i class="fas fa-file-invoice me-2 text-success"></i>Convertir en facture
                                            </a>
                                        </li>
                                        @endcan
                                        @can('quotes.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('quotes.edit', $quote) }}">
                                                <i class="fas fa-edit me-2 text-muted"></i>Modifier
                                            </a>
                                        </li>
                                        @endcan
                                    @endif
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quotes.duplicate', $quote) }}">
                                            <i class="fas fa-copy me-2 text-muted"></i>Dupliquer
                                        </a>
                                    </li>
                                    @can('quotes.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('quotes.destroy', $quote) }}" method="POST" onsubmit="return confirm('Supprimer ce devis ?')">
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
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucun devis trouvé</p>
                            @can('quotes.create')
                            <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer le premier devis
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($quotes->hasPages())
    <div class="card-footer bg-transparent">
        {{ $quotes->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
// Fix dropdown clipping inside table-responsive overflow container
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
