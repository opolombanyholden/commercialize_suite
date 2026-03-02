@extends('layouts.admin')
@section('title', $session->name)
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.sessions.index') }}">Inventaires</a></li>
<li class="breadcrumb-item active">{{ Str::limit($session->name, 40) }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title mb-1">{{ $session->name }}</h1>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="badge bg-{{ $session->statusBadgeClass() }} fs-6">{{ $session->statusLabel() }}</span>
            <span class="text-muted">{{ $session->date->format('d/m/Y') }}</span>
            @if($session->site)
                <span class="badge bg-light text-dark border"><i class="fas fa-warehouse me-1"></i>{{ $session->site->name }}</span>
            @endif
        </div>
    </div>
    @if($session->isEditable())
    <form action="{{ route('inventory.sessions.complete', $session) }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-success"
                onclick="return confirm('Clôturer cet inventaire ? Les stocks seront mis à jour automatiquement.')">
            <i class="fas fa-check-circle me-2"></i>Clôturer l'inventaire
        </button>
    </form>
    @endif
</div>
@endsection

@section('content')
@php
    $totalLines    = $session->lines->count();
    $countedLines  = $session->lines->filter(fn($l) => $l->isCounted())->count();
    $progress      = $totalLines > 0 ? round($countedLines / $totalLines * 100) : 0;
    $totalGood     = $session->lines->sum('good_quantity');
    $totalDamaged  = $session->lines->sum('damaged_quantity');
@endphp

{{-- Progress & Stats --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">Progression du comptage</span>
                    <span class="text-muted">{{ $countedLines }} / {{ $totalLines }} produits</span>
                </div>
                <div class="progress mb-2" style="height: 12px;">
                    <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-4 text-center">
                        <p class="text-muted mb-1 small">Bon état</p>
                        <h4 class="mb-0 text-success">{{ number_format($totalGood) }}</h4>
                    </div>
                    <div class="col-4 text-center">
                        <p class="text-muted mb-1 small">Mauvais état</p>
                        <h4 class="mb-0 text-danger">{{ number_format($totalDamaged) }}</h4>
                    </div>
                    <div class="col-4 text-center">
                        <p class="text-muted mb-1 small">Non comptés</p>
                        <h4 class="mb-0 text-secondary">{{ $totalLines - $countedLines }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <p class="text-muted small mb-3">{{ $session->notes }}</p>
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Créé par</td>
                        <td>{{ $session->user->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date</td>
                        <td>{{ $session->date->format('d/m/Y') }}</td>
                    </tr>
                    @if($session->completed_at)
                    <tr>
                        <td class="text-muted">Clôturé</td>
                        <td>{{ $session->completed_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Lignes d'inventaire --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-list me-2 text-primary"></i>Lignes d'inventaire</h5>
        @if($session->isEditable())
        <div class="input-group" style="max-width: 250px;">
            <input type="text" id="searchLine" class="form-control form-control-sm" placeholder="Filtrer produit…">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="inventoryTable">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>SKU</th>
                        <th class="text-center">Stock théorique</th>
                        @if($session->isEditable())
                        <th class="text-center">Bon état</th>
                        <th class="text-center">Mauvais état</th>
                        <th class="text-center">Écart</th>
                        <th>Notes</th>
                        <th class="text-end">Action</th>
                        @else
                        <th class="text-center">Bon état</th>
                        <th class="text-center">Mauvais état</th>
                        <th class="text-center">Total compté</th>
                        <th class="text-center">Écart</th>
                        <th>Notes</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($session->lines as $line)
                    @php
                        $counted = ($line->good_quantity ?? 0) + ($line->damaged_quantity ?? 0);
                        $discrepancy = $line->isCounted() ? ($counted - $line->expected_quantity) : null;
                    @endphp
                    <tr class="inventory-row {{ $line->isCounted() ? 'table-light' : '' }}" data-name="{{ strtolower($line->product->name) }}">
                        <td>
                            <a href="{{ route('products.show', $line->product) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $line->product->name }}
                            </a>
                            @if(!$line->isCounted() && $session->isEditable())
                                <span class="badge bg-warning text-dark ms-1 small">À compter</span>
                            @elseif($line->isCounted())
                                <span class="badge bg-success ms-1 small"><i class="fas fa-check"></i></span>
                            @endif
                        </td>
                        <td class="text-muted small"><code>{{ $line->product->sku ?? '—' }}</code></td>
                        <td class="text-center">{{ $line->expected_quantity }}</td>

                        @if($session->isEditable())
                        {{-- Formulaire de saisie --}}
                        <td colspan="5">
                            <form action="{{ route('inventory.sessions.line', [$session, $line]) }}" method="POST"
                                  class="d-flex gap-2 align-items-center flex-wrap">
                                @csrf @method('PATCH')
                                <div style="width: 90px;">
                                    <input type="number" name="good_quantity" class="form-control form-control-sm text-center"
                                           value="{{ $line->good_quantity ?? '' }}" min="0" placeholder="Bon état"
                                           title="Quantité en bon état">
                                </div>
                                <div style="width: 90px;">
                                    <input type="number" name="damaged_quantity" class="form-control form-control-sm text-center border-danger"
                                           value="{{ $line->damaged_quantity ?? 0 }}" min="0" placeholder="Casse"
                                           title="Quantité en mauvais état">
                                </div>
                                @if($discrepancy !== null)
                                <span class="badge bg-{{ $discrepancy == 0 ? 'success' : ($discrepancy > 0 ? 'info' : 'danger') }} fs-6">
                                    {{ $discrepancy > 0 ? '+' : '' }}{{ $discrepancy }}
                                </span>
                                @endif
                                <div style="min-width: 130px; flex:1;">
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                           value="{{ $line->notes ?? '' }}" placeholder="Notes">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary" title="Enregistrer">
                                    <i class="fas fa-save"></i>
                                </button>
                            </form>
                        </td>
                        @else
                        <td class="text-center text-success fw-semibold">{{ $line->good_quantity ?? '—' }}</td>
                        <td class="text-center text-danger fw-semibold">{{ $line->damaged_quantity ?? '—' }}</td>
                        <td class="text-center fw-bold">{{ $line->isCounted() ? $counted : '—' }}</td>
                        <td class="text-center">
                            @if($discrepancy !== null)
                                <span class="badge bg-{{ $discrepancy == 0 ? 'success' : ($discrepancy > 0 ? 'info' : 'danger') }}">
                                    {{ $discrepancy > 0 ? '+' : '' }}{{ $discrepancy }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $line->notes ?? '—' }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@if($session->isEditable())
@push('scripts')
<script>
document.getElementById('searchLine').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.inventory-row').forEach(function (row) {
        row.style.display = row.dataset.name.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endif
