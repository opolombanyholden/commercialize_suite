@extends('layouts.admin')
@section('title', 'Corbeille — Mouvements de stock')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stocks</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.movements.index') }}">Mouvements</a></li>
<li class="breadcrumb-item active">Corbeille</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-trash-alt me-2 text-danger"></i>Corbeille — Mouvements de stock</h1>
        <p class="text-muted mb-0">{{ $movements->total() }} mouvement(s) supprimé(s)</p>
    </div>
    <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour aux mouvements
    </a>
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Produit</th>
                        <th>Type</th>
                        <th class="text-center">Quantité</th>
                        <th>Référence</th>
                        <th>Supprimé le</th>
                        <th class="text-end" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                    <tr>
                        <td class="text-muted small">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $movement->product?->name ?? 'Produit supprimé' }}</td>
                        <td>
                            <span class="badge bg-{{ \App\Models\StockMovement::typeBadgeClass($movement->type) }}">
                                {{ \App\Models\StockMovement::typeLabel($movement->type) }}
                            </span>
                        </td>
                        <td class="text-center fw-bold {{ $movement->quantity > 0 ? 'text-success' : 'text-danger' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </td>
                        <td class="text-muted small">{{ $movement->reference ?? '—' }}</td>
                        <td class="text-muted small">{{ $movement->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end">
                            <form action="{{ route('inventory.movements.restore', $movement->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-undo me-1"></i>Restaurer</button>
                            </form>
                            @role('company_admin')
                            <form action="{{ route('inventory.movements.forceDelete', $movement->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Supprimer DEFINITIVEMENT ce mouvement ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Supprimer</button>
                            </form>
                            @endrole
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-trash fa-3x mb-3 d-block"></i>La corbeille est vide.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($movements->hasPages())
    <div class="card-footer bg-transparent">{{ $movements->links() }}</div>
    @endif
</div>
@endsection
