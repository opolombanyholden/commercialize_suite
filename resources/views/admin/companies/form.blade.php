@extends('layouts.admin')

@section('title', isset($company) ? 'Modifier l\'entreprise' : 'Nouvelle entreprise')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.companies.index') }}">Entreprises</a></li>
        <li class="breadcrumb-item active">{{ isset($company) ? 'Modifier' : 'Nouvelle' }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                <i class="fas fa-building me-2"></i>{{ isset($company) ? 'Modifier l\'entreprise' : 'Nouvelle entreprise' }}
            </h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<form action="{{ isset($company) ? route('admin.companies.update', $company) : route('admin.companies.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($company))
        @method('PUT')
    @endif

    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom commercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $company->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="legal_name" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control @error('legal_name') is-invalid @enderror" id="legal_name" name="legal_name" value="{{ old('legal_name', $company->legal_name ?? '') }}">
                            @error('legal_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $company->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $company->phone ?? '') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $company->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Code postal</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $company->postal_code ?? '') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $company->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label">Pays</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" id="country" name="country" value="{{ old('country', $company->country ?? 'Gabon') }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fiscal Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informations fiscales</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">Numéro d'identification fiscale (NIF)</label>
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" name="tax_id" value="{{ old('tax_id', $company->tax_id ?? '') }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="registration_number" class="form-label">Numéro RCCM</label>
                            <input type="text" class="form-control @error('registration_number') is-invalid @enderror" id="registration_number" name="registration_number" value="{{ old('registration_number', $company->registration_number ?? '') }}">
                            @error('registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Coordonnées bancaires</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">Nom de la banque</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name" name="bank_name" value="{{ old('bank_name', $company->bank_name ?? '') }}">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_account" class="form-label">Numéro de compte</label>
                            <input type="text" class="form-control @error('bank_account') is-invalid @enderror" id="bank_account" name="bank_account" value="{{ old('bank_account', $company->bank_account ?? '') }}">
                            @error('bank_account')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_iban" class="form-label">IBAN</label>
                            <input type="text" class="form-control @error('bank_iban') is-invalid @enderror" id="bank_iban" name="bank_iban" value="{{ old('bank_iban', $company->bank_iban ?? '') }}">
                            @error('bank_iban')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_swift" class="form-label">Code SWIFT</label>
                            <input type="text" class="form-control @error('bank_swift') is-invalid @enderror" id="bank_swift" name="bank_swift" value="{{ old('bank_swift', $company->bank_swift ?? '') }}">
                            @error('bank_swift')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Logo -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Logo</h5>
                </div>
                <div class="card-body text-center">
                    <div class="logo-preview mb-3" id="logoPreview">
                        @if(isset($company) && $company->logo_path)
                            <img src="{{ Storage::url($company->logo_path) }}" alt="Logo" id="previewImg">
                        @else
                            <i class="fas fa-building fa-3x text-muted"></i>
                            <p class="text-muted mb-0 mt-2">Aucun logo</p>
                        @endif
                    </div>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">JPG, PNG ou GIF. Max 2 Mo.</small>
                </div>
            </div>

            <!-- Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Paramètres</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="version" class="form-label">Version</label>
                        <select class="form-select @error('version') is-invalid @enderror" id="version" name="version">
                            <option value="light" {{ old('version', $company->version ?? '') === 'light' ? 'selected' : '' }}>Light</option>
                            <option value="standard" {{ old('version', $company->version ?? '') === 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="pro" {{ old('version', $company->version ?? '') === 'pro' ? 'selected' : '' }}>Pro</option>
                            <option value="enterprise" {{ old('version', $company->version ?? 'enterprise') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                        @error('version')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="currency" class="form-label">Devise</label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                            <option value="XAF" {{ old('currency', $company->currency ?? 'XAF') === 'XAF' ? 'selected' : '' }}>XAF - Franc CFA</option>
                            <option value="EUR" {{ old('currency', $company->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                            <option value="USD" {{ old('currency', $company->currency ?? '') === 'USD' ? 'selected' : '' }}>USD - Dollar US</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $company->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Entreprise active</label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-save me-2"></i>{{ isset($company) ? 'Mettre à jour' : 'Créer l\'entreprise' }}
                    </button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary w-100">
                        Annuler
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
<style>
.logo-preview {
    width: 150px;
    height: 150px;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    overflow: hidden;
}
.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}
</style>
@endpush

@push('scripts')
<script>
function previewLogo(input) {
    const preview = document.getElementById('logoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<img src="' + e.target.result + '" id="previewImg">';
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
