@extends('layouts.admin')

@section('title', $company->name)

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Entreprises</a></li>
        <li class="breadcrumb-item active">{{ $company->name }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                @if($company->logo_path)
                    <img src="{{ Storage::url($company->logo_path) }}" alt="Logo" class="me-3" style="height: 50px; border-radius: 8px;">
                @endif
                {{ $company->name }}
            </h1>
            <p class="page-subtitle">{{ $company->legal_name }}</p>
        </div>
        <div class="col-auto">
            @can('companies.edit')
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            @endcan
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Info Cards -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations générales</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Email</label>
                        <p class="mb-0">{{ $company->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Téléphone</label>
                        <p class="mb-0">{{ $company->phone ?: '-' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="text-muted small">Adresse</label>
                        <p class="mb-0">{{ $company->full_address ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sites -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Sites ({{ $company->sites->count() }})</h5>
                @can('sites.create')
                <a href="{{ route('admin.sites.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Adresse</th>
                                <th>Principal</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->sites as $site)
                            <tr>
                                <td>{{ $site->name }}</td>
                                <td>{{ $site->city }}</td>
                                <td>
                                    @if($site->is_headquarters)
                                        <span class="badge bg-primary">Siège</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $site->is_active ? 'success' : 'secondary' }}">
                                        {{ $site->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Aucun site</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Utilisateurs ({{ $company->users->count() }})</h5>
                @can('users.create')
                <a href="{{ route('admin.users.create', ['company_id' => $company->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus me-1"></i>Ajouter
                </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Rôle</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($company->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach($user->roles as $role)
                                        <span class="badge" style="background: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                        {{ $user->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Aucun utilisateur</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Version</span>
                    <span class="badge bg-{{ $company->version === 'enterprise' ? 'danger' : ($company->version === 'pro' ? 'warning' : 'info') }}">
                        {{ ucfirst($company->version) }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Devise</span>
                    <span>{{ $company->currency }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Sites</span>
                    <span class="fw-bold">{{ $company->sites->count() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Utilisateurs</span>
                    <span class="fw-bold">{{ $company->users->count() }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Statut</span>
                    <span class="badge bg-{{ $company->is_active ? 'success' : 'secondary' }}">
                        {{ $company->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Fiscal Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informations fiscales</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">NIF</label>
                    <p class="mb-0">{{ $company->tax_id ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">RCCM</label>
                    <p class="mb-0">{{ $company->registration_number ?: '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Banque</label>
                    <p class="mb-0">{{ $company->bank_name ?: '-' }}</p>
                </div>
                <div>
                    <label class="text-muted small">Compte</label>
                    <p class="mb-0">{{ $company->bank_account ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
