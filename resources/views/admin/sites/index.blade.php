@extends('layouts.admin')

@section('title', 'Sites')

@section('breadcrumb')
<li class="breadcrumb-item active">Sites</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Gestion des sites</h1>
        <p class="text-muted mb-0">Points de vente et succursales</p>
    </div>
    @can('sites.create')
    <a href="{{ route('admin.sites.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouveau site
    </a>
    @endcan
</div>
@endsection

@section('content')

{{-- Filtres --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.sites.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Recherche</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                       placeholder="Nom, code, ville...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select class="form-select" name="type" onchange="this.form.submit()">
                    <option value="">Tous les types</option>
                    <option value="headquarters" {{ request('type') === 'headquarters' ? 'selected' : '' }}>Siège principal</option>
                    <option value="store"        {{ request('type') === 'store'        ? 'selected' : '' }}>Points de vente</option>
                    <option value="warehouse"    {{ request('type') === 'warehouse'    ? 'selected' : '' }}>Entrepôts</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Statut</label>
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Actifs</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-search me-1"></i>Chercher
                </button>
                @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
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
                        <th>Site</th>
                        <th>Type</th>
                        <th>Adresse</th>
                        <th>Responsable</th>
                        <th class="text-center">Utilisateurs</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                    <tr>
                        <td>
                            <a href="{{ route('admin.sites.show', $site) }}" class="fw-semibold text-decoration-none">
                                {{ $site->name }}
                                @if($site->is_headquarters)
                                    <i class="fas fa-star text-warning ms-1" title="Siège principal"></i>
                                @endif
                            </a>
                            @if($site->code)
                                <br><code class="small text-muted">{{ $site->code }}</code>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if($site->is_store)
                                    <span class="badge bg-info"><i class="fas fa-store me-1"></i>Vente</span>
                                @endif
                                @if($site->is_warehouse)
                                    <span class="badge bg-secondary"><i class="fas fa-warehouse me-1"></i>Stock</span>
                                @endif
                                @if(!$site->is_store && !$site->is_warehouse)
                                    <span class="text-muted">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $site->address ? Str::limit($site->address, 25) : '' }}
                                @if($site->city)<br>{{ $site->city }}@endif
                            </small>
                        </td>
                        <td>
                            @if($site->manager)
                                <small>{{ $site->manager->name }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $site->users_count }}</span>
                        </td>
                        <td class="text-center">
                            @if($site->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-outline-secondary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('sites.edit')
                                <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('sites.delete')
                                @if(!$site->is_headquarters)
                                <form action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Supprimer le site {{ addslashes($site->name) }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-map-marker-alt fa-3x mb-3 d-block"></i>
                            @if(request()->hasAny(['search', 'type', 'status']))
                                Aucun site ne correspond aux critères.
                                <br><a href="{{ route('admin.sites.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Voir tous les sites</a>
                            @else
                                Aucun site enregistré.
                                @can('sites.create')
                                <br><a href="{{ route('admin.sites.create') }}" class="btn btn-sm btn-primary mt-2">
                                    <i class="fas fa-plus me-1"></i>Créer le premier site
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
    @if($sites->hasPages())
    <div class="card-footer bg-transparent">
        {{ $sites->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
