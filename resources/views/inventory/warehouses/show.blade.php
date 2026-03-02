@extends('layouts.admin')
@section('title', $warehouse->name)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.warehouses.index') }}">Entrepôts</a></li>
<li class="breadcrumb-item active">{{ $warehouse->name }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">{{ $warehouse->name }}</h1>
        <div class="d-flex gap-2 align-items-center">
            <code>{{ $warehouse->code }}</code>
            @if($warehouse->is_active)
                <span class="badge bg-success">Actif</span>
            @else
                <span class="badge bg-secondary">Inactif</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('inventory.movements.create', ['site_id' => $warehouse->id]) }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau mouvement
        </a>
        @can('products.edit')
        <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" class="btn btn-outline-secondary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{{-- Stats --}}
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-arrow-down"></i></div>
                <div>
                    <p class="text-muted mb-1 small">Total entrées</p>
                    <h4 class="mb-0 fw-bold text-success">+{{ number_format($stats['total_in']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10 text-danger"><i class="fas fa-arrow-up"></i></div>
                <div>
                    <p class="text-muted mb-1 small">Total sorties</p>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($stats['total_out']) }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-exchange-alt"></i></div>
                <div>
                    <p class="text-muted mb-1 small">Mouvements</p>
                    <h4 class="mb-0 fw-bold">{{ number_format($stats['total_movements']) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Derniers mouvements</h5>
                <a href="{{ route('inventory.movements.index', ['site_id' => $warehouse->id]) }}" class="btn btn-sm btn-outline-secondary">
                    Voir tout
                </a>
            </div>
            <div class="card-body p-0">
                @if($recentMovements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Produit</th>
                                <th>Type</th>
                                <th class="text-center">Quantité</th>
                                <th>Référence</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMovements as $movement)
                            <tr>
                                <td class="text-muted small">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('products.show', $movement->product) }}" class="text-decoration-none">
                                        {{ $movement->product->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ \App\Models\StockMovement::typeBadgeClass($movement->type) }}">
                                        {{ \App\Models\StockMovement::typeLabel($movement->type) }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                                </td>
                                <td class="text-muted small">{{ $movement->reference ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2"></i>
                    <p class="mb-0">Aucun mouvement enregistré.</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted ps-3">Code</td><td class="pe-3"><code>{{ $warehouse->code }}</code></td></tr>
                    <tr><td class="text-muted ps-3">Adresse</td><td class="pe-3">{{ $warehouse->address ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Ville</td><td class="pe-3">{{ $warehouse->city ?? '—' }}</td></tr>
                    <tr><td class="text-muted ps-3">Créé le</td><td class="pe-3">{{ $warehouse->created_at->format('d/m/Y') }}</td></tr>
                </table>
            </div>
        </div>

        @can('products.delete')
        <form action="{{ route('inventory.warehouses.destroy', $warehouse) }}" method="POST" id="delete-warehouse-form" class="d-none">
            @csrf @method('DELETE')
        </form>
        <button type="button" class="btn btn-outline-danger w-100"
                onclick="confirmDelete('delete-warehouse-form', 'Supprimer l\'entrepôt « {{ addslashes($warehouse->name) }} » ?')">
            <i class="fas fa-trash me-2"></i>Supprimer l'entrepôt
        </button>
        @endcan
    </div>
</div>
@endsection
