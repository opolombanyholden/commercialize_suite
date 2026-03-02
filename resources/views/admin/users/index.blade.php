@extends('layouts.admin')

@section('title', 'Utilisateurs')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Administration</a></li>
<li class="breadcrumb-item active">Utilisateurs</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Gestion des utilisateurs</h1>
        <p class="text-muted mb-0">{{ $users->total() }} utilisateur(s)</p>
    </div>
    @can('users.create')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-2"></i>Nouvel utilisateur
    </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Filters --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" name="search" placeholder="Rechercher..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="role">
                    <option value="">Tous les rôles</option>
                    @foreach($roles ?? [] as $role)
                        <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactifs</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-filter me-1"></i>Filtrer</button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Utilisateur</th>
                        <th>Entreprise</th>
                        <th>Rôle</th>
                        <th>Sites</th>
                        <th class="text-center">Dernière connexion</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3 bg-{{ $user->is_active ? 'primary' : 'secondary' }} bg-opacity-10">
                                    @if($user->avatar_path)
                                        <img src="{{ Storage::url($user->avatar_path) }}" alt="" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <span class="text-{{ $user->is_active ? 'primary' : 'secondary' }} fw-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                    <br><small class="text-muted">{{ $user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->company->name ?? '-' }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            @if($user->sites->count() > 0)
                                <span class="badge bg-light text-dark">{{ $user->sites->count() }} site(s)</span>
                            @else
                                <span class="text-muted">Tous</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->last_login_at)
                                <small>{{ $user->last_login_at->diffForHumans() }}</small>
                            @else
                                <small class="text-muted">Jamais</small>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge bg-success">Actif</span>
                            @else
                                <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('users.edit')
                                    <li><a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}"><i class="fas fa-edit me-2"></i>Modifier</a></li>
                                    @endcan
                                    @can('users.impersonate')
                                    @if($user->id !== auth()->id())
                                    <li><a class="dropdown-item" href="{{ route('admin.users.impersonate', $user) }}"><i class="fas fa-user-secret me-2"></i>Se connecter en tant que</a></li>
                                    @endif
                                    @endcan
                                    <li><hr class="dropdown-divider"></li>
                                    @if($user->is_active)
                                    <li>
                                        <form action="{{ route('admin.users.deactivate', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-warning"><i class="fas fa-ban me-2"></i>Désactiver</button>
                                        </form>
                                    </li>
                                    @else
                                    <li>
                                        <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-success"><i class="fas fa-check me-2"></i>Activer</button>
                                        </form>
                                    </li>
                                    @endif
                                    @can('users.delete')
                                    <li>
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Supprimer</button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-3x mb-3"></i>
                            <p>Aucun utilisateur trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-transparent">{{ $users->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
.avatar { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
</style>
@endpush
