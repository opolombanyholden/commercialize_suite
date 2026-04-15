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
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form id="roleForm" method="POST" action="{{ route('admin.roles.store') }}" style="display: contents;">
                @csrf
                <div id="roleMethodField" style="display: none;"></div>

                <div class="modal-header">
                    <h5 class="modal-title" id="roleModalLabel"><i class="fas fa-user-shield me-2"></i>Nouveau rôle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="roleName" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="roleName" name="name" required
                               placeholder="ex: comptable_junior">
                        <small class="text-muted">Identifiant technique. Lettres minuscules et underscores recommandés.</small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0 fw-semibold">
                            <i class="fas fa-key me-1 text-warning"></i>Permissions
                        </label>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="permsCheckAll">
                                <i class="fas fa-check-double me-1"></i>Tout cocher
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="permsUncheckAll">
                                <i class="fas fa-times me-1"></i>Tout décocher
                            </button>
                        </div>
                    </div>

                    <div id="permissionsContainer">
                        @foreach($allPermissions as $module => $perms)
                            <div class="card border mb-2">
                                <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                    <strong class="text-uppercase small">{{ $module }}</strong>
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input module-check" type="checkbox"
                                               id="module-{{ $module }}" data-module="{{ $module }}">
                                        <label class="form-check-label small text-muted" for="module-{{ $module }}">
                                            Module entier
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body py-2">
                                    <div class="row g-2">
                                        @foreach($perms as $permission)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input perm-check"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->id }}"
                                                           id="perm-{{ $permission->id }}"
                                                           data-module="{{ $module }}">
                                                    <label class="form-check-label small" for="perm-{{ $permission->id }}">
                                                        {{ Str::after($permission->name, '.') }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
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

function uncheckAllPermissions() {
    document.querySelectorAll('#permissionsContainer .perm-check').forEach(cb => cb.checked = false);
    document.querySelectorAll('#permissionsContainer .module-check').forEach(cb => cb.checked = false);
}

function checkPermissionsByIds(ids) {
    const set = new Set(ids.map(String));
    document.querySelectorAll('#permissionsContainer .perm-check').forEach(cb => {
        cb.checked = set.has(cb.value);
    });
    syncModuleCheckboxes();
}

function syncModuleCheckboxes() {
    document.querySelectorAll('#permissionsContainer .module-check').forEach(modCb => {
        const module = modCb.dataset.module;
        const perms = document.querySelectorAll(`#permissionsContainer .perm-check[data-module="${module}"]`);
        const allChecked = perms.length > 0 && Array.from(perms).every(p => p.checked);
        modCb.checked = allChecked;
    });
}

function resetRoleForm() {
    document.getElementById('roleForm').action = '{{ route("admin.roles.store") }}';
    document.getElementById('roleName').value = '';
    document.getElementById('roleModalLabel').innerHTML = '<i class="fas fa-user-shield me-2"></i>Nouveau rôle';
    document.getElementById('roleMethodField').innerHTML = '';
    uncheckAllPermissions();
}

function editRole(role) {
    document.getElementById('roleForm').action = '/admin/roles/' + role.id;
    document.getElementById('roleMethodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('roleModalLabel').innerHTML = '<i class="fas fa-edit me-2"></i>Modifier le rôle';
    document.getElementById('roleName').value = role.name || '';

    uncheckAllPermissions();
    const permIds = (role.permissions || []).map(p => p.id);
    checkPermissionsByIds(permIds);

    roleModal.show();
}

function viewPermissions(role) {
    document.getElementById('permissionsRoleName').textContent = role.name;

    const permissions = role.permissions || [];
    let html = '';

    if (permissions.length === 0) {
        html = '<p class="text-muted text-center">Aucune permission assignée à ce rôle.</p>';
    } else {
        const grouped = {};
        permissions.forEach(p => {
            const [module] = p.name.split('.');
            if (!grouped[module]) grouped[module] = [];
            grouped[module].push(p.name);
        });

        for (const [module, perms] of Object.entries(grouped)) {
            html += `<div class="mb-3"><h6 class="text-uppercase text-muted">${module} <span class="badge bg-secondary">${perms.length}</span></h6><div class="d-flex flex-wrap gap-1">`;
            perms.forEach(p => {
                html += `<span class="badge bg-primary">${p}</span>`;
            });
            html += '</div></div>';
        }
    }

    document.getElementById('permissionsList').innerHTML = html;
    permissionsModal.show();
}

// Cocher / décocher tout
document.getElementById('permsCheckAll')?.addEventListener('click', function () {
    document.querySelectorAll('#permissionsContainer .perm-check').forEach(cb => cb.checked = true);
    syncModuleCheckboxes();
});
document.getElementById('permsUncheckAll')?.addEventListener('click', uncheckAllPermissions);

// Cocher tout un module
document.querySelectorAll('#permissionsContainer .module-check').forEach(modCb => {
    modCb.addEventListener('change', function () {
        const module = this.dataset.module;
        document.querySelectorAll(`#permissionsContainer .perm-check[data-module="${module}"]`)
            .forEach(cb => cb.checked = this.checked);
    });
});

// Mettre à jour la case "module entier" quand une perm change
document.querySelectorAll('#permissionsContainer .perm-check').forEach(cb => {
    cb.addEventListener('change', syncModuleCheckboxes);
});
</script>
@endpush
