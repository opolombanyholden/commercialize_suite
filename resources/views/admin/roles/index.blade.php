@extends('layouts.admin')

@section('title', 'Rôles et permissions')

@section('breadcrumb')
<li class="breadcrumb-item active">Rôles</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Rôles et permissions</h1>
        <p class="text-muted mb-0">Gérez les niveaux d'accès des utilisateurs</p>
    </div>
    @can('roles.create')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="resetRoleForm()">
        <i class="fas fa-plus me-2"></i>Nouveau rôle
    </button>
    @endcan
</div>
@endsection

@section('content')
{{-- Roles Grid --}}
<div class="row g-4">
    @foreach($roles as $role)
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <span class="badge fs-6" style="background-color: {{ $role->color ?? '#6c757d' }}">
                    {{ $role->name }}
                </span>
                @if($role->is_system_role ?? false)
                    <span class="badge bg-light text-muted"><i class="fas fa-lock me-1"></i>Système</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">{{ $role->description ?? 'Aucune description' }}</p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="fas fa-users me-1"></i>{{ $role->users_count ?? 0 }} utilisateur(s)
                    </span>
                    <span class="text-muted small">
                        <i class="fas fa-key me-1"></i>{{ $role->permissions_count ?? 0 }} permission(s)
                    </span>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1" onclick="viewPermissions({{ json_encode($role) }})">
                        <i class="fas fa-eye me-1"></i>Permissions
                    </button>
                    @if(!($role->is_system_role ?? false))
                        @can('roles.edit')
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editRole({{ json_encode($role) }})">
                            <i class="fas fa-edit"></i>
                        </button>
                        @endcan
                        @can('roles.delete')
                        @if(($role->users_count ?? 0) == 0)
                        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce rôle ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Role Modal --}}
<div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div id="roleMethodField"></div>
                
                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel"><i class="fas fa-user-shield me-2"></i>Nouveau rôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="roleDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="roleDescription" name="description" rows="2"></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="roleLevel" class="form-label">Niveau (1-100)</label>
                            <input type="number" class="form-control" id="roleLevel" name="level" min="1" max="100" value="50">
                        </div>
                        <div class="col-md-6">
                            <label for="roleColor" class="form-label">Couleur</label>
                            <input type="color" class="form-control form-control-color w-100" id="roleColor" name="color" value="#6c757d">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="roleSubmitBtn"><i class="fas fa-save me-2"></i>Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Permissions Modal --}}
<div class="modal fade" id="permissionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-key me-2"></i>Permissions : <span id="permissionsRoleName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="permissionsList"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const roleModal = new bootstrap.Modal(document.getElementById('roleModal'));
const permissionsModal = new bootstrap.Modal(document.getElementById('permissionsModal'));

function resetRoleForm() {
    document.getElementById('roleForm').action = '{{ route("admin.roles.store") }}';
    document.getElementById('roleForm').reset();
    document.getElementById('roleModalLabel').innerHTML = '<i class="fas fa-user-shield me-2"></i>Nouveau rôle';
    document.getElementById('roleMethodField').innerHTML = '';
}

function editRole(role) {
    document.getElementById('roleForm').action = '/admin/roles/' + role.id;
    document.getElementById('roleMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('roleModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier le rôle';
    document.getElementById('roleName').value = role.name || '';
    document.getElementById('roleDescription').value = role.description || '';
    document.getElementById('roleLevel').value = role.level || 50;
    document.getElementById('roleColor').value = role.color || '#6c757d';
    roleModal.show();
}

function viewPermissions(role) {
    document.getElementById('permissionsRoleName').textContent = role.name;
    
    const permissions = role.permissions || [];
    let html = '';
    
    if (permissions.length === 0) {
        html = '<p class="text-muted text-center">Aucune permission spécifique (accès complet ou aucun)</p>';
    } else {
        const grouped = {};
        permissions.forEach(p => {
            const [module] = p.name.split('.');
            if (!grouped[module]) grouped[module] = [];
            grouped[module].push(p.name);
        });
        
        for (const [module, perms] of Object.entries(grouped)) {
            html += `<div class="mb-3"><h6 class="text-uppercase text-muted">${module}</h6><div class="d-flex flex-wrap gap-1">`;
            perms.forEach(p => {
                html += `<span class="badge bg-primary">${p}</span>`;
            });
            html += '</div></div>';
        }
    }
    
    document.getElementById('permissionsList').innerHTML = html;
    permissionsModal.show();
}
</script>
@endpush
