@extends('layouts.admin')
@section('title', 'Inventaires')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item active">Inventaires</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Sessions d'inventaire</h1>
        <p class="text-muted mb-0">Comptage physique des stocks</p>
    </div>
    @can('products.edit')
    <a href="{{ route('inventory.sessions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvel inventaire
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @if($inventories->count() > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Date</th>
                        <th>Entrepôt</th>
                        <th class="text-center">Lignes</th>
                        <th class="text-center">Statut</th>
                        <th>Créé par</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventories as $inv)
                    <tr>
                        <td>
                            <a href="{{ route('inventory.sessions.show', $inv) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $inv->name }}
                            </a>
                        </td>
                        <td>{{ $inv->date->format('d/m/Y') }}</td>
                        <td>{{ $inv->site->name ?? '—' }}</td>
                        <td class="text-center"><span class="badge bg-secondary">{{ $inv->lines_count }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $inv->statusBadgeClass() }}">{{ $inv->statusLabel() }}</span>
                        </td>
                        <td class="text-muted small">{{ $inv->user->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('inventory.sessions.show', $inv) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-{{ $inv->status === 'completed' ? 'eye' : 'clipboard-list' }}"></i>
                                {{ $inv->status === 'completed' ? 'Voir' : 'Saisir' }}
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($inventories->hasPages())
        <div class="card-footer bg-transparent">{{ $inventories->links() }}</div>
        @endif
        @else
        <div class="text-center py-5 text-muted">
            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
            <h5>Aucun inventaire</h5>
            <p>Créez votre premier inventaire physique.</p>
            @can('products.edit')
            <a href="{{ route('inventory.sessions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvel inventaire
            </a>
            @endcan
        </div>
        @endif
    </div>
</div>
@endsection
