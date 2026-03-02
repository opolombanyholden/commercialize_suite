@extends('layouts.admin')

@section('title', 'Bons de livraison')

@section('breadcrumb')
<li class="breadcrumb-item active">Bons de livraison</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Bons de livraison</h1>
        <p class="text-muted mb-0">{{ $deliveries->total() }} bon(s) au total</p>
    </div>
    @can('deliveries.create')
    <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau bon
    </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-warning border-4">
            <div class="card-body">
                <p class="text-muted mb-1">En attente</p>
                <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-info border-4">
            <div class="card-body">
                <p class="text-muted mb-1">En transit</p>
                <h4 class="mb-0 text-info">{{ $stats['in_transit'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-success border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Livrés</p>
                <h4 class="mb-0 text-success">{{ $stats['delivered'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm border-start border-primary border-4">
            <div class="card-body">
                <p class="text-muted mb-1">Total</p>
                <h4 class="mb-0">{{ $stats['total'] }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('deliveries.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="N° BL, client..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="pending"    {{ request('status') == 'pending'    ? 'selected' : '' }}>En attente</option>
                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>En transit</option>
                    <option value="delivered"  {{ request('status') == 'delivered'  ? 'selected' : '' }}>Livré</option>
                    <option value="cancelled"  {{ request('status') == 'cancelled'  ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('deliveries.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° BL</th>
                        <th>Client</th>
                        <th>Facture</th>
                        <th>Date prévue</th>
                        <th>Livreur</th>
                        <th class="text-center">Statut</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td>
                            <a href="{{ route('deliveries.show', $delivery) }}" class="fw-semibold text-decoration-none">
                                {{ $delivery->delivery_number }}
                            </a>
                        </td>
                        <td>{{ Str::limit($delivery->client_name, 30) }}</td>
                        <td>
                            @if($delivery->invoice)
                                <a href="{{ route('invoices.show', $delivery->invoice) }}" class="small text-decoration-none">
                                    {{ $delivery->invoice->invoice_number }}
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td>{{ $delivery->planned_date->format('d/m/Y') }}</td>
                        <td>{{ $delivery->livreur ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $delivery->status_color }}">{{ $delivery->status_label }}</span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle table-dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('deliveries.show', $delivery) }}">
                                            <i class="fas fa-eye me-2 text-muted"></i>Voir
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('deliveries.pdf', $delivery) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-2 text-danger"></i>Télécharger PDF
                                        </a>
                                    </li>
                                    @if(!$delivery->isDelivered())
                                        @can('deliveries.edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('deliveries.edit', $delivery) }}">
                                                <i class="fas fa-edit me-2 text-muted"></i>Modifier
                                            </a>
                                        </li>
                                        @endcan
                                    @endif
                                    @can('deliveries.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('deliveries.destroy', $delivery) }}" method="POST" onsubmit="return confirm('Supprimer ce bon de livraison ?')">
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
                            <i class="fas fa-truck fa-3x text-muted mb-3 d-block"></i>
                            <p class="text-muted mb-3">Aucun bon de livraison trouvé</p>
                            @can('deliveries.create')
                            <a href="{{ route('deliveries.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer le premier bon
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($deliveries->hasPages())
    <div class="card-footer bg-transparent">
        {{ $deliveries->links() }}
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
