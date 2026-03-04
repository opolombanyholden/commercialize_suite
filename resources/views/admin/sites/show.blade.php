@extends('layouts.admin')

@section('title', $site->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Sites</a></li>
<li class="breadcrumb-item active">{{ $site->name }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">
            {{ $site->name }}
            @if($site->is_headquarters)
                <i class="fas fa-star text-warning ms-2" title="Siège principal"></i>
            @endif
        </h1>
        <p class="text-muted mb-0">
            @if($site->code)<code>{{ $site->code }}</code> ·@endif
            @if($site->city){{ $site->city }}@endif
            <span class="ms-2 badge bg-{{ $site->is_active ? 'success' : 'secondary' }}">
                {{ $site->is_active ? 'Actif' : 'Inactif' }}
            </span>
        </p>
    </div>
    <div class="d-flex gap-2">
        @can('sites.edit')
        <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i>Modifier
        </a>
        @endcan
        <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row">

    {{-- Left column --}}
    <div class="col-lg-8">

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-primary">{{ $stats['users_count'] }}</div>
                        <div class="text-muted small">Utilisateurs</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-info">{{ $stats['quotes_count'] }}</div>
                        <div class="text-muted small">Devis</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-success">{{ $stats['invoices_count'] }}</div>
                        <div class="text-muted small">Factures</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <div class="card border-0 shadow-sm text-center h-100">
                    <div class="card-body py-3">
                        <div class="fs-3 fw-bold text-warning">{{ number_format($stats['total_revenue'], 0, ',', ' ') }}</div>
                        <div class="text-muted small">Revenu (FCFA)</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Infos détaillées --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Nom</div>
                            <div class="fw-semibold">{{ $site->name }}</div>
                        </div>
                        @if($site->code)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Code</div>
                            <code>{{ $site->code }}</code>
                        </div>
                        @endif
                        @if($site->phone)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Téléphone</div>
                            <div>{{ $site->phone }}</div>
                        </div>
                        @endif
                        @if($site->email)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Email</div>
                            <div>{{ $site->email }}</div>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($site->full_address)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Adresse</div>
                            <div>{{ $site->full_address }}</div>
                        </div>
                        @endif
                        @if($site->description)
                        <div class="mb-3">
                            <div class="text-muted small mb-1">Description</div>
                            <div class="text-muted">{{ $site->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Type badges --}}
                <div class="d-flex gap-2 flex-wrap mt-2">
                    @if($site->is_headquarters)
                        <span class="badge bg-warning text-dark"><i class="fas fa-star me-1"></i>Siège principal</span>
                    @endif
                    @if($site->is_store)
                        <span class="badge bg-info"><i class="fas fa-store me-1"></i>Point de vente</span>
                    @endif
                    @if($site->is_warehouse)
                        <span class="badge bg-secondary"><i class="fas fa-warehouse me-1"></i>Entrepôt</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Utilisateurs du site --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0"><i class="fas fa-users me-2 text-primary"></i>Utilisateurs ({{ $stats['users_count'] }})</h6>
                @can('sites.edit')
                <a href="{{ route('admin.sites.users', $site) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-user-cog me-1"></i>Gérer
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                @if($site->users->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-user-slash fa-2x mb-2"></i>
                        <p class="mb-0">Aucun utilisateur assigné</p>
                    </div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($site->users->take(5) as $user)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <div class="avatar avatar-sm bg-primary">
                                <span class="avatar-text">{{ substr($user->name, 0, 2) }}</span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $user->name }}</div>
                                <small class="text-muted">{{ $user->roles->first()?->name ?? 'Aucun rôle' }}</small>
                            </div>
                        </li>
                        @endforeach
                        @if($site->users->count() > 5)
                        <li class="list-group-item text-center text-muted small">
                            + {{ $site->users->count() - 5 }} autre(s)
                        </li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>

    </div>

    {{-- Right column —— Actions --}}
    <div class="col-lg-4">

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">

                    @can('sites.edit')
                    <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Modifier le site
                    </a>
                    <a href="{{ route('admin.sites.users', $site) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-users me-2"></i>Gérer les utilisateurs
                    </a>
                    <form action="{{ route('admin.sites.toggle-status', $site) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn w-100 {{ $site->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
                            <i class="fas fa-{{ $site->is_active ? 'pause' : 'play' }} me-2"></i>
                            {{ $site->is_active ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    @endcan

                    @can('sites.delete')
                    @if(!$site->is_headquarters)
                    <form action="{{ route('admin.sites.destroy', $site) }}" method="POST"
                          onsubmit="return confirm('Supprimer ce site définitivement ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </form>
                    @else
                    <button class="btn btn-outline-danger w-100" disabled title="Impossible de supprimer le siège principal">
                        <i class="fas fa-lock me-2"></i>Siège — non supprimable
                    </button>
                    @endif
                    @endcan

                </div>
            </div>
        </div>

        {{-- Responsable --}}
        @if($site->manager)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0"><i class="fas fa-user-tie me-2 text-primary"></i>Responsable</h6>
            </div>
            <div class="card-body d-flex align-items-center gap-3">
                <div class="avatar avatar-sm bg-primary">
                    <span class="avatar-text">{{ substr($site->manager->name, 0, 2) }}</span>
                </div>
                <div>
                    <div class="fw-semibold">{{ $site->manager->name }}</div>
                    <small class="text-muted">{{ $site->manager->email }}</small>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
