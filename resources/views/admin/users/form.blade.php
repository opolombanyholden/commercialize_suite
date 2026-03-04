<form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($user))
        @method('PUT')
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            {{-- Basic Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="job_title" class="form-label">Fonction</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title ?? '') }}" placeholder="Ex: Directeur commercial">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Password --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-lock me-2 text-primary"></i>Mot de passe</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">
                                Mot de passe 
                                @if(!isset($user))<span class="text-danger">*</span>@endif
                            </label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ !isset($user) ? 'required' : '' }}>
                            @if(isset($user))
                                <div class="form-text">Laissez vide pour conserver le mot de passe actuel</div>
                            @endif
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Roles & Permissions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Rôle et permissions</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Rôle <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            @foreach($roles ?? [] as $role)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="role_{{ $role->id }}" value="{{ $role->name }}" 
                                        {{ old('role', isset($user) ? $user->roles->first()?->name : '') == $role->name ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        <span class="badge" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                                        <br><small class="text-muted">{{ $role->description ?? '' }}</small>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
            
            {{-- Site Access --}}
            @feature('multi_sites')
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Accès aux sites</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="access_all_sites" name="access_all_sites" value="1" 
                            {{ old('access_all_sites', isset($user) && $user->sites->isEmpty() ? 1 : 0) ? 'checked' : '' }}>
                        <label class="form-check-label" for="access_all_sites">
                            <strong>Accès à tous les sites</strong>
                        </label>
                    </div>
                    <div id="sitesList" style="{{ old('access_all_sites', isset($user) && $user->sites->isEmpty() ? 1 : 0) ? 'display: none;' : '' }}">
                        <label class="form-label">Sélectionner les sites accessibles :</label>
                        <div class="row g-2">
                            @foreach($sites ?? [] as $site)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="sites[]" id="site_{{ $site->id }}" value="{{ $site->id }}"
                                        {{ in_array($site->id, old('sites', isset($user) ? $user->sites->pluck('id')->toArray() : [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="site_{{ $site->id }}">
                                        {{ $site->name }}
                                        @if($site->is_headquarters)<i class="fas fa-star text-warning ms-1" title="Siège"></i>@endif
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endfeature
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Avatar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Photo de profil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        @if(isset($user) && $user->avatar_path)
                            <img src="{{ Storage::url($user->avatar_path) }}" alt="" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" id="avatarPreview">
                        @else
                            <div class="avatar-placeholder mx-auto mb-2" id="avatarPreview">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                    <div class="form-text">JPG, PNG. Max 2 Mo</div>
                </div>
            </div>
            
            {{-- Status --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Statut</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                            {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Compte actif</label>
                    </div>
                    <div class="form-text">Les comptes inactifs ne peuvent pas se connecter.</div>
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>{{ isset($user) ? 'Mettre à jour' : 'Créer l\'utilisateur' }}
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('styles')
<style>
.avatar-placeholder { width: 100px; height: 100px; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@push('scripts')
<script>
document.getElementById('access_all_sites')?.addEventListener('change', function() {
    document.getElementById('sitesList').style.display = this.checked ? 'none' : '';
});
</script>
@endpush
