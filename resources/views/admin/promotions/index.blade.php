@extends('layouts.admin')

@section('title', 'Promotions')

@section('breadcrumb')
<li class="breadcrumb-item active">Promotions</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Promotions & Codes promo</h1>
        <p class="text-muted mb-0">Remises applicables aux devis et factures</p>
    </div>
    @can('promotions.create')
    <a href="{{ route('promotions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle promotion
    </a>
    @endcan
</div>
@endsection

@section('content')

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('promotions.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Recherche</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                       placeholder="Code ou nom de promotion...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Toutes</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actives</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactives</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-search me-1"></i>Chercher
                </button>
                @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                    <i class="fas fa-times"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Tableau --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom</th>
                        <th>Remise</th>
                        <th>Validité</th>
                        <th class="text-center">Utilisations</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($promotions as $promo)
                    <tr>
                        <td>
                            <code class="fw-bold text-primary fs-6">{{ $promo->code }}</code>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $promo->name }}</div>
                            @if($promo->description)
                                <small class="text-muted">{{ Str::limit($promo->description, 50) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $promo->discount_type === 'percent' ? 'info' : 'warning' }} text-dark">
                                @if($promo->discount_type === 'percent')
                                    <i class="fas fa-percent me-1"></i>{{ $promo->discount_value }}%
                                @else
                                    <i class="fas fa-minus me-1"></i>{{ number_format($promo->discount_value, 0, ',', ' ') }} FCFA
                                @endif
                            </span>
                            @if($promo->min_amount)
                                <br><small class="text-muted">Min : {{ number_format($promo->min_amount, 0, ',', ' ') }} FCFA</small>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                @if($promo->valid_from || $promo->valid_until)
                                    {{ $promo->valid_from?->format('d/m/Y') ?? '—' }}
                                    → {{ $promo->valid_until?->format('d/m/Y') ?? '∞' }}
                                @else
                                    <span class="text-success">Illimitée</span>
                                @endif
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                {{ $promo->uses_count }}{{ $promo->max_uses ? ' / ' . $promo->max_uses : '' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($promo->is_active)
                                @if($promo->valid_until && $promo->valid_until->isPast())
                                    <span class="badge bg-warning text-dark">Expirée</span>
                                @elseif($promo->max_uses && $promo->uses_count >= $promo->max_uses)
                                    <span class="badge bg-secondary">Épuisée</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                @can('promotions.edit')
                                <a href="{{ route('promotions.edit', $promo) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('promotions.delete')
                                @if($promo->uses_count === 0)
                                <form action="{{ route('promotions.destroy', $promo) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer la promotion {{ addslashes($promo->code) }} ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-outline-secondary" disabled title="Déjà utilisée, impossible de supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-tag fa-3x mb-3 d-block"></i>
                            @if(request()->hasAny(['search', 'status']))
                                Aucune promotion ne correspond aux critères.
                                <br><a href="{{ route('promotions.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Voir toutes</a>
                            @else
                                Aucune promotion créée.
                                @can('promotions.create')
                                <br><a href="{{ route('promotions.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Créer la première promotion
                                </a>
                                @endcan
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($promotions->hasPages())
    <div class="card-footer bg-transparent">
        {{ $promotions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
