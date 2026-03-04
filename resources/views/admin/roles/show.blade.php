@extends('layouts.admin')

@section('title', $role->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Rôles</a></li>
<li class="breadcrumb-item active">{{ $role->name }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">
            <span class="badge fs-5 me-2" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
        </h1>
        <p class="text-muted mb-0">{{ $role->permissions->count() }} permission(s) · {{ $users->count() }} utilisateur(s)</p>
    </div>
    <div class="d-flex gap-2">
        @if(!in_array($role->name, ['super_admin', 'company_admin']))
        @can('roles.edit')
        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        @endcan
        @endif
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="row g-4">
    {{-- Permissions par module --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-key me-2 text-primary"></i>Permissions</h5>
            </div>
            <div class="card-body">
                @forelse($permissionsByModule as $module => $modulePermissions)
                <div class="mb-3">
                    <h6 class="text-uppercase text-muted mb-2">{{ $module }}</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($modulePermissions as $permission)
                        <span class="badge bg-primary">{{ $permission->name }}</span>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)<hr>@endif
                @empty
                <p class="text-muted text-center mb-0">Aucune permission assignée à ce rôle.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Utilisateurs avec ce rôle --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-users me-2 text-primary"></i>Utilisateurs</h5>
            </div>
            <div class="card-body p-0">
                @forelse($users as $user)
                <div class="d-flex align-items-center p-3 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                         style="width:36px; height:36px;">
                        <span class="text-primary fw-bold small">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                    </div>
                    <div>
                        <div class="fw-semibold small">{{ $user->name }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $user->email }}</div>
                    </div>
                    @if(!$user->is_active)
                    <span class="badge bg-secondary ms-auto">Inactif</span>
                    @endif
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-slash fa-2x mb-2"></i>
                    <p class="small mb-0">Aucun utilisateur avec ce rôle</p>
                </div>
                @endforelse
            </div>
        </div>

        @if(!in_array($role->name, ['super_admin', 'company_admin', 'site_manager', 'accountant', 'sales_manager', 'salesperson', 'warehouse_manager', 'viewer']))
        @can('roles.delete')
        @if($users->count() === 0)
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                      onsubmit="return confirm('Supprimer le rôle « {{ $role->name }} » ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="fas fa-trash me-2"></i>Supprimer ce rôle
                    </button>
                </form>
            </div>
        </div>
        @endif
        @endcan
        @endif
    </div>
</div>
@endsection
