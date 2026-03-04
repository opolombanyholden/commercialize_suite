<div class="row">
    <div class="col-lg-8">
        {{-- Informations --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-user-shield me-2 text-primary"></i>Informations du rôle</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="name" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                           id="name" name="name"
                           value="{{ old('name', $role->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Permissions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-key me-2 text-primary"></i>Permissions</h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleAllPerms">
                    Tout sélectionner
                </button>
            </div>
            <div class="card-body">
                @foreach($permissions as $module => $modulePermissions)
                <div class="mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <h6 class="text-uppercase text-muted mb-0 me-2">{{ $module }}</h6>
                        <button type="button" class="btn btn-xs btn-link text-muted p-0 small module-select-all"
                                data-module="{{ $module }}">tout</button>
                    </div>
                    <div class="row g-2">
                        @foreach($modulePermissions as $permission)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input perm-checkbox perm-{{ $module }}"
                                       type="checkbox"
                                       name="permissions[]"
                                       id="perm_{{ $permission->id }}"
                                       value="{{ $permission->id }}"
                                       {{ in_array($permission->id, old('permissions', $rolePermissions ?? [])) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="perm_{{ $permission->id }}">
                                    {{ $permission->name }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)<hr>@endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Actions --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>{{ isset($role) ? 'Mettre à jour' : 'Créer le rôle' }}
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('toggleAllPerms')?.addEventListener('click', function() {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    const allChecked = [...checkboxes].every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    this.textContent = allChecked ? 'Tout sélectionner' : 'Tout désélectionner';
});

document.querySelectorAll('.module-select-all').forEach(btn => {
    btn.addEventListener('click', function() {
        const module = this.dataset.module;
        const checkboxes = document.querySelectorAll('.perm-' + module);
        const allChecked = [...checkboxes].every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
    });
});
</script>
@endpush
