@extends('layouts.admin')

@section('title', 'Modifier mon profil')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Mon profil</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        {{-- Personal Information --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>Informations personnelles
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Avatar --}}
                        <div class="col-12 text-center mb-3">
                            <div class="position-relative d-inline-block">
                                @if(auth()->user()->avatar_path)
                                    <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="" class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;" id="avatarPreview">
                                @else
                                    <div class="avatar-lg mx-auto bg-primary bg-opacity-10 text-primary" id="avatarPreview">
                                        <span class="fs-3 fw-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                                <label for="avatar" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0; cursor: pointer;">
                                    <i class="fas fa-camera" style="line-height: 32px;"></i>
                                </label>
                                <input type="file" id="avatar" name="avatar" accept="image/*" class="d-none">
                            </div>
                            <div class="form-text mt-2">JPG ou PNG. Max 2 Mo</div>
                        </div>
                        
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        {{-- Email --}}
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="+241 XX XX XX XX">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        {{-- Job Title --}}
                        <div class="col-md-6">
                            <label for="job_title" class="form-label">Fonction</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title', auth()->user()->job_title) }}" placeholder="Ex: Directeur commercial">
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>
        
        {{-- Change Password --}}
        <form action="{{ route('profile.password') }}" method="POST" id="password">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lock me-2 text-primary"></i>Changer le mot de passe
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="current_password" class="form-label">Mot de passe actuel <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                            @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Nouveau mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            <div class="form-text">Minimum 8 caractères</div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-key me-2"></i>Changer le mot de passe
                    </button>
                </div>
            </div>
        </form>
        
        {{-- Preferences --}}
        <form action="{{ route('profile.preferences') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2 text-primary"></i>Préférences
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="language" class="form-label">Langue</label>
                            <select class="form-select" id="language" name="language">
                                <option value="fr" {{ (auth()->user()->language ?? 'fr') == 'fr' ? 'selected' : '' }}>Français</option>
                                <option value="en" {{ (auth()->user()->language ?? 'fr') == 'en' ? 'selected' : '' }}>English</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="timezone" class="form-label">Fuseau horaire</label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="Africa/Libreville" {{ (auth()->user()->timezone ?? 'Africa/Libreville') == 'Africa/Libreville' ? 'selected' : '' }}>Libreville (GMT+1)</option>
                                <option value="Europe/Paris" {{ (auth()->user()->timezone ?? '') == 'Europe/Paris' ? 'selected' : '' }}>Paris (GMT+1/+2)</option>
                                <option value="UTC" {{ (auth()->user()->timezone ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" {{ auth()->user()->email_notifications ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_notifications">
                                    Recevoir les notifications par email
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-save me-2"></i>Enregistrer les préférences
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    {{-- Sidebar --}}
    <div class="col-lg-4">
        {{-- Account Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h6 class="card-title mb-0">Informations du compte</h6>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted">Entreprise</td>
                        <td class="text-end fw-semibold">{{ auth()->user()->company->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Version</td>
                        <td class="text-end">
                            <span class="badge bg-{{ auth()->user()->company->version === 'enterprise' ? 'danger' : 'primary' }}">
                                {{ ucfirst(auth()->user()->company->version ?? 'standard') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Rôle</td>
                        <td class="text-end">
                            @foreach(auth()->user()->roles as $role)
                                <span class="badge" style="background-color: {{ $role->color ?? '#6c757d' }}">{{ $role->name }}</span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Créé le</td>
                        <td class="text-end">{{ auth()->user()->created_at->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        {{-- Danger Zone --}}
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="card-title mb-0 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Zone de danger
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    La suppression de votre compte est irréversible. Toutes vos données personnelles seront effacées.
                </p>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    <i class="fas fa-trash me-2"></i>Supprimer mon compte
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Delete Account Modal --}}
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Supprimer mon compte
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Attention !</strong> Cette action est irréversible.
                    </div>
                    <p>Pour confirmer la suppression, veuillez saisir votre mot de passe :</p>
                    <input type="password" class="form-control" name="password" placeholder="Votre mot de passe" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Supprimer définitivement
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-lg {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('avatar').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatarPreview');
            if (preview.tagName === 'IMG') {
                preview.src = e.target.result;
            } else {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-circle';
                img.style.cssText = 'width: 100px; height: 100px; object-fit: cover;';
                img.id = 'avatarPreview';
                preview.parentNode.replaceChild(img, preview);
            }
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
@endpush
