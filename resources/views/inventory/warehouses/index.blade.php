@extends('layouts.admin')

@section('title', 'Entrepôts')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item active">Entrepôts</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Entrepôts</h1>
        <p class="text-muted mb-0">Gérer vos entrepôts et lieux de stockage</p>
    </div>
    @can('products.create')
    <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvel entrepôt
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($warehouses->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Code</th>
                        <th>Ville</th>
                        <th class="text-center">Mouvements</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warehouses as $warehouse)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar bg-primary bg-opacity-10">
                                    <i class="fas fa-warehouse text-primary"></i>
                                </div>
                                <div>
                                    <a href="{{ route('inventory.warehouses.show', $warehouse) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $warehouse->name }}
                                    </a>
                                    @if($warehouse->address)
                                        <div class="text-muted small">{{ $warehouse->address }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td><code>{{ $warehouse->code ?? '—' }}</code></td>
                        <td>{{ $warehouse->city ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $warehouse->stock_movements_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($warehouse->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('inventory.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('products.edit')
                            <a href="{{ route('inventory.warehouses.edit', $warehouse) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($warehouses->hasPages())
        <div class="card-footer bg-transparent">
            {{ $warehouses->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-5">
            <i class="fas fa-warehouse fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Aucun entrepôt configuré</h5>
            <p class="text-muted">Créez votre premier entrepôt pour commencer à gérer vos stocks.</p>
            @can('products.create')
            <a href="{{ route('inventory.warehouses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Créer un entrepôt
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
