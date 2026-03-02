@extends('layouts.admin')

@section('title', 'Sites')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administration</a></li>
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
{{-- Filter by Company --}}
@if(auth()->user()->hasRole('super_admin'))
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.sites.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <select class="form-select" name="company" onchange="this.form.submit()">
                    <option value="">Toutes les entreprises</option>
                    @foreach($companies ?? [] as $company)
                        <option value="{{ $company->id }}" {{ request('company') == $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Site</th>
                        <th>Entreprise</th>
                        <th>Adresse</th>
                        <th>Responsable</th>
                        <th class="text-center">Principal</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                    <tr>
                        <td>
                            <span class="fw-semibold">{{ $site->name }}</span>
                            @if($site->code)
                                <br><code class="small">{{ $site->code }}</code>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.companies.show', $site->company_id) }}" class="text-decoration-none">
                                {{ $site->company->name }}
                            </a>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $site->address ? Str::limit($site->address, 30) : '-' }}
                                @if($site->city), {{ $site->city }}@endif
                            </small>
                        </td>
                        <td>
                            @if($site->manager)
                                {{ $site->manager->name }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($site->is_headquarters)
                                <i class="fas fa-star text-warning" title="Siège principal"></i>
                            @else
                                <span class="text-muted">-</span>
                            @endif
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
                                @can('sites.edit')
                                <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-outline-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('sites.delete')
                                @if(!$site->is_headquarters)
                                <form action="{{ route('admin.sites.destroy', $site) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce site ?')">
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
                            <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
                            <p>Aucun site enregistré</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($sites->hasPages())
    <div class="card-footer bg-transparent">{{ $sites->links() }}</div>
    @endif
</div>
@endsection
