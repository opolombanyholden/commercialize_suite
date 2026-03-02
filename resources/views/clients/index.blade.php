@extends('layouts.admin')

@section('title', 'Clients')

@section('breadcrumb')
<li class="breadcrumb-item active">Clients</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Clients</h1>
        <p class="text-muted mb-0">{{ $clients->total() }} client(s) au total</p>
    </div>
    <div class="d-flex gap-2">
        @can('clients.create')
        <a href="{{ route('clients.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Nouveau client
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('clients.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, email, téléphone..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">Tous types</option>
                    <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Particulier</option>
                    <option value="business" {{ request('type') == 'business' ? 'selected' : '' }}>Entreprise</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Plus récents</option>
                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Nom A-Z</option>
                    <option value="revenue" {{ request('sort') == 'revenue' ? 'selected' : '' }}>CA décroissant</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">
                    <i class="fas fa-filter me-1"></i>Filtrer
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-times me-1"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Clients Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Factures</th>
                        <th class="text-end">Chiffre d'affaires</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-{{ $client->type === 'business' ? 'primary' : 'info' }} bg-opacity-10">
                                    <i class="fas fa-{{ $client->type === 'business' ? 'building' : 'user' }} text-{{ $client->type === 'business' ? 'primary' : 'info' }}"></i>
                                </div>
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="fw-semibold text-decoration-none">
                                        {{ $client->name }}
                                    </a>
                                    @if($client->business_name && $client->type === 'individual')
                                        <br><small class="text-muted">{{ $client->business_name }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($client->email)
                                <a href="mailto:{{ $client->email }}" class="text-decoration-none">
                                    <i class="fas fa-envelope me-1 text-muted"></i>{{ $client->email }}
                                </a><br>
                            @endif
                            @if($client->phone)
                                <a href="tel:{{ $client->phone }}" class="text-decoration-none text-muted">
                                    <i class="fas fa-phone me-1"></i>{{ $client->phone }}
                                </a>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $client->type === 'business' ? 'primary' : 'info' }}">
                                {{ $client->type === 'business' ? 'Entreprise' : 'Particulier' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark">{{ $client->invoices_count ?? 0 }}</span>
                        </td>
                        <td class="text-end fw-semibold">
                            {{ number_format($client->total_revenue ?? 0, 0, ',', ' ') }} <small class="text-muted">FCFA</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle table-dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('clients.show', $client) }}">
                                            <i class="fas fa-eye me-2 text-muted"></i>Voir
                                        </a>
                                    </li>
                                    @can('clients.edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('clients.edit', $client) }}">
                                            <i class="fas fa-edit me-2 text-muted"></i>Modifier
                                        </a>
                                    </li>
                                    @endcan
                                    @can('invoices.create')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('invoices.create', ['client_id' => $client->id]) }}">
                                            <i class="fas fa-file-invoice me-2 text-muted"></i>Nouvelle facture
                                        </a>
                                    </li>
                                    @endcan
                                    @can('clients.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Supprimer ce client ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucun client trouvé</p>
                            @can('clients.create')
                            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Créer le premier client
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($clients->hasPages())
    <div class="card-footer bg-transparent">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table-dropdown-toggle').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.avatar {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush
