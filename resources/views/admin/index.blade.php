@extends('layouts.admin')

@section('title', 'Administration')

@section('breadcrumb')
<li class="breadcrumb-item active">Administration</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Administration système</h1>
        <p class="text-muted mb-0">Gestion des entreprises, utilisateurs et paramètres</p>
    </div>
</div>
@endsection

@section('content')
{{-- Quick Stats --}}
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $stats['companies'] ?? 0 }}</h2>
                        <p class="text-muted mb-0">Entreprises</p>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $stats['sites'] ?? 0 }}</h2>
                        <p class="text-muted mb-0">Sites</p>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $stats['users'] ?? 0 }}</h2>
                        <p class="text-muted mb-0">Utilisateurs</p>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-0">{{ $stats['roles'] ?? 0 }}</h2>
                        <p class="text-muted mb-0">Rôles</p>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Admin Modules --}}
<div class="row g-4">
    {{-- Companies --}}
    @can('companies.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-primary bg-opacity-10 text-primary me-3">
                        <i class="fas fa-building fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Entreprises</h5>
                </div>
                <p class="text-muted small mb-3">Gérer les entreprises clientes, leurs informations et abonnements.</p>
                <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-primary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
    
    {{-- Sites --}}
    @can('sites.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-success bg-opacity-10 text-success me-3">
                        <i class="fas fa-map-marker-alt fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Sites</h5>
                </div>
                <p class="text-muted small mb-3">Gérer les points de vente, succursales et entrepôts.</p>
                <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-success w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
    
    {{-- Users --}}
    @can('users.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-info bg-opacity-10 text-info me-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Utilisateurs</h5>
                </div>
                <p class="text-muted small mb-3">Gérer les comptes utilisateurs, rôles et permissions.</p>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-info w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
    
    {{-- Roles --}}
    @can('roles.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning me-3">
                        <i class="fas fa-shield-alt fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Rôles & Permissions</h5>
                </div>
                <p class="text-muted small mb-3">Configurer les niveaux d'accès et permissions.</p>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-warning w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
    
    {{-- Settings --}}
    @can('settings.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-secondary bg-opacity-10 text-secondary me-3">
                        <i class="fas fa-cog fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Paramètres</h5>
                </div>
                <p class="text-muted small mb-3">Configuration générale de l'application.</p>
                <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
    
    {{-- Audit Logs --}}
    @can('audit.view')
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box bg-danger bg-opacity-10 text-danger me-3">
                        <i class="fas fa-history fa-lg"></i>
                    </div>
                    <h5 class="card-title mb-0">Journal d'audit</h5>
                </div>
                <p class="text-muted small mb-3">Historique des actions et modifications.</p>
                <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-danger w-100">
                    <i class="fas fa-arrow-right me-2"></i>Accéder
                </a>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection

@push('styles')
<style>
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.icon-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
