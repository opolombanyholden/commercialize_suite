@extends('layouts.admin')
@section('title', 'Mouvements de stock')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item active">Mouvements</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Mouvements de stock</h1>
        <p class="text-muted mb-0">Historique des entrées et sorties</p>
    </div>
    @can('products.edit')
    <a href="{{ route('inventory.movements.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau mouvement
    </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted">Produit</label>
                <select name="product_id" class="form-select form-select-sm">
                    <option value="">Tous les produits</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Entrepôt</label>
                <select name="site_id" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ request('site_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Du</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Au</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($movements->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th class="text-center">Quantité</th>
                        <th class="text-center">Avant</th>
                        <th class="text-center">Après</th>
                        <th>Entrepôt</th>
                        <th>Référence</th>
                        <th>Opérateur</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                    <tr>
                        <td class="text-muted small">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('products.show', $movement->product) }}" class="text-decoration-none fw-semibold">
                                {{ $movement->product->name }}
                            </a>
                            @if($movement->product->sku)
                                <div class="text-muted small">{{ $movement->product->sku }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ \App\Models\StockMovement::typeBadgeClass($movement->type) }}">
                                <i class="fas {{ \App\Models\StockMovement::typeIcon($movement->type) }} me-1"></i>
                                {{ \App\Models\StockMovement::typeLabel($movement->type) }}
                            </span>
                        </td>
                        <td class="text-center fw-bold fs-6 {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="text-center text-muted">{{ $movement->stock_before }}</td>
                        <td class="text-center fw-semibold">{{ $movement->stock_after }}</td>
                        <td class="text-muted small">{{ $movement->site->name ?? '—' }}</td>
                        <td class="text-muted small">{{ $movement->reference ?? '—' }}</td>
                        <td class="text-muted small">{{ $movement->user->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer bg-transparent">
            {{ $movements->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-exchange-alt fa-3x mb-3"></i>
            <h5>Aucun mouvement de stock</h5>
            <p>Enregistrez votre premier mouvement de stock.</p>
            @can('products.edit')
            <a href="{{ route('inventory.movements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouveau mouvement
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
