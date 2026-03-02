@extends('layouts.admin')

@section('title', 'Entreprises')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administration</a></li>
<li class="breadcrumb-item active">Entreprises</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Gestion des entreprises</h1>
        <p class="text-muted mb-0">{{ $companies->total() }} entreprise(s) enregistrée(s)</p>
    </div>
    @can('companies.create')
    <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nouvelle entreprise
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Entreprise</th>
                        <th>Contact</th>
                        <th class="text-center">Version</th>
                        <th class="text-center">Sites</th>
                        <th class="text-center">Utilisateurs</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($company->logo_path)
                                    <img src="{{ Storage::url($company->logo_path) }}" alt="" class="me-3" style="width: 40px; height: 40px; object-fit: contain;">
                                @else
                                    <div class="avatar me-3 bg-primary bg-opacity-10">
                                        <i class="fas fa-building text-primary"></i>
                                    </div>
                                @endif
                                <div>
                                    <span class="fw-semibold">{{ $company->name }}</span>
                                    @if($company->legal_name)
                                        <br><small class="text-muted">{{ $company->legal_name }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <small>
                                @if($company->email)<i class="fas fa-envelope me-1 text-muted"></i>{{ $company->email }}<br>@endif
                                @if($company->phone)<i class="fas fa-phone me-1 text-muted"></i>{{ $company->phone }}@endif
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $company->version === 'enterprise' ? 'danger' : ($company->version === 'pro' ? 'warning' : ($company->version === 'standard' ? 'info' : 'secondary')) }}">
                                {{ ucfirst($company->version) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $company->sites_count ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $company->users_count ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            @if($company->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.companies.show', $company) }}"><i class="fas fa-eye me-2"></i>Voir</a></li>
                                    @can('companies.edit')
                                    <li><a class="dropdown-item" href="{{ route('admin.companies.edit', $company) }}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                    @endcan
                                    <li><a class="dropdown-item" href="{{ route('admin.sites.index', ['company' => $company->id]) }}"><i class="fas fa-map-marker-alt me-2"></i>Sites</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index', ['company' => $company->id]) }}"><i class="fas fa-users me-2"></i>Utilisateurs</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-building fa-3x mb-3"></i>
                            <p>Aucune entreprise enregistrée</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($companies->hasPages())
    <div class="card-footer bg-transparent">{{ $companies->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
.avatar { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
</style>
@endpush
