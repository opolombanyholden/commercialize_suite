@extends('layouts.admin')
@section('title', 'Gestion des stocks')
@section('breadcrumb')
<li class="breadcrumb-item active">Stocks</li>
@endsection

@section('page-header')
<div>
    <h1 class="page-title mb-1">Gestion des stocks</h1>
    <p class="text-muted mb-0">Vue d'ensemble de vos stocks</p>
</div>
@endsection

@section('content')
{{-- Raccourcis modules --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="{{ route('inventory.warehouses.index') }}" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-4">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary" style="width:56px;height:56px;font-size:1.5rem;">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div>
                    <h5 class="mb-1 text-dark">Entrepôts</h5>
                    <p class="text-muted mb-0 small">Gérer vos lieux de stockage</p>
                </div>
                <i class="fas fa-chevron-right text-muted ms-auto"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('inventory.movements.index') }}" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-4">
                <div class="stat-icon bg-success bg-opacity-10 text-success" style="width:56px;height:56px;font-size:1.5rem;">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h5 class="mb-1 text-dark">Mouvements</h5>
                    <p class="text-muted mb-0 small">Entrées et sorties de stock</p>
                </div>
                <i class="fas fa-chevron-right text-muted ms-auto"></i>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="{{ route('inventory.sessions.index') }}" class="card border-0 shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-4 p-4">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning" style="width:56px;height:56px;font-size:1.5rem;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div>
                    <h5 class="mb-1 text-dark">Inventaires</h5>
                    <p class="text-muted mb-0 small">Comptage physique des stocks</p>
                </div>
                <i class="fas fa-chevron-right text-muted ms-auto"></i>
            </div>
        </a>
    </div>
</div>

{{-- Produits en rupture / stock bas --}}
@if($lowStockProducts->count() > 0)
<div class="card border-0 shadow-sm mb-4 border-start border-warning border-3">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 text-warning"><i class="fas fa-exclamation-triangle me-2"></i>Alertes stock</h5>
        <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="btn btn-sm btn-outline-warning">Voir tous</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th class="text-center">Stock actuel</th>
                        <th class="text-center">Seuil d'alerte</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockProducts as $product)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="text-decoration-none fw-semibold">
                                {{ $product->name }}
                            </a>
                        </td>
                        <td class="text-center fw-bold {{ $product->stock_quantity <= 0 ? 'text-danger' : 'text-warning' }}">
                            {{ $product->stock_quantity }}
                        </td>
                        <td class="text-center text-muted">{{ $product->stock_alert_threshold }}</td>
                        <td class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger">Épuisé</span>
                            @else
                                <span class="badge bg-warning text-dark">Stock faible</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('inventory.movements.create', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus me-1"></i>Entrée
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- Derniers mouvements --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-history me-2 text-primary"></i>Derniers mouvements</h5>
        <a href="{{ route('inventory.movements.index') }}" class="btn btn-sm btn-outline-secondary">Voir tout</a>
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
            <p class="mb-0">Aucun mouvement enregistré.</p>
        </div>
        @endif
    </div>
</div>
@endsection
