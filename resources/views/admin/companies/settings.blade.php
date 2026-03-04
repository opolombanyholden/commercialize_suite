@extends('layouts.admin')

@section('title', 'Paramètres de l\'entreprise')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item active">Paramètres de l'entreprise</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title">
                <i class="fas fa-building me-2"></i>Paramètres de l'entreprise
            </h1>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('settings.company.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $company->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="legal_name" class="form-label">Raison sociale</label>
                            <input type="text" class="form-control @error('legal_name') is-invalid @enderror"
                                   id="legal_name" name="legal_name" value="{{ old('legal_name', $company->legal_name) }}">
                            @error('legal_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $company->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $company->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="website" class="form-label">Site web</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   id="website" name="website" value="{{ old('website', $company->website) }}"
                                   placeholder="https://www.exemple.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="2">{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Code postal</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
                                   id="postal_code" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city" value="{{ old('city', $company->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="country" class="form-label">Pays (code 2 lettres)</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country" value="{{ old('country', $company->country) }}"
                                   maxlength="2" placeholder="GA">
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
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror"
                                   id="tax_id" name="tax_id" value="{{ old('tax_id', $company->tax_id) }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="registration_number" class="form-label">Numéro RCCM</label>
                            <input type="text" class="form-control @error('registration_number') is-invalid @enderror"
                                   id="registration_number" name="registration_number"
                                   value="{{ old('registration_number', $company->registration_number) }}">
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
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror"
                                   id="bank_name" name="bank_name" value="{{ old('bank_name', $company->bank_name) }}">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_account" class="form-label">Numéro de compte</label>
                            <input type="text" class="form-control @error('bank_account') is-invalid @enderror"
                                   id="bank_account" name="bank_account" value="{{ old('bank_account', $company->bank_account) }}">
                            @error('bank_account')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="iban" class="form-label">IBAN</label>
                            <input type="text" class="form-control @error('iban') is-invalid @enderror"
                                   id="iban" name="iban" value="{{ old('iban', $company->iban) }}">
                            @error('iban')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="swift" class="form-label">Code SWIFT</label>
                            <input type="text" class="form-control @error('swift') is-invalid @enderror"
                                   id="swift" name="swift" value="{{ old('swift', $company->swift) }}">
                            @error('swift')
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
                        @if($company->logo_path)
                            <img src="{{ Storage::url($company->logo_path) }}" alt="Logo" id="previewImg">
                        @else
                            <i class="fas fa-building fa-3x text-muted"></i>
                            <p class="text-muted mb-0 mt-2">Aucun logo</p>
                        @endif
                    </div>
                    <input type="file" class="form-control @error('logo') is-invalid @enderror"
                           id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">JPG, PNG ou GIF. Max 2 Mo.</small>
                    @if($company->logo_path)
                        <div class="form-check mt-2 text-start">
                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                            <label class="form-check-label text-danger small" for="remove_logo">Supprimer le logo actuel</label>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Préférences -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Préférences</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="currency" class="form-label">Devise</label>
                        <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                            <option value="XAF" {{ old('currency', $company->currency) === 'XAF' ? 'selected' : '' }}>XAF — Franc CFA</option>
                            <option value="EUR" {{ old('currency', $company->currency) === 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                            <option value="USD" {{ old('currency', $company->currency) === 'USD' ? 'selected' : '' }}>USD — Dollar US</option>
                        </select>
                        @error('currency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-0">
                        <label for="timezone" class="form-label">Fuseau horaire</label>
                        <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                            <option value="Africa/Libreville" {{ old('timezone', $company->timezone) === 'Africa/Libreville' ? 'selected' : '' }}>Africa/Libreville (UTC+1)</option>
                            <option value="Africa/Douala"     {{ old('timezone', $company->timezone) === 'Africa/Douala'     ? 'selected' : '' }}>Africa/Douala (UTC+1)</option>
                            <option value="Africa/Lagos"      {{ old('timezone', $company->timezone) === 'Africa/Lagos'      ? 'selected' : '' }}>Africa/Lagos (UTC+1)</option>
                            <option value="Africa/Dakar"      {{ old('timezone', $company->timezone) === 'Africa/Dakar'      ? 'selected' : '' }}>Africa/Dakar (UTC+0)</option>
                            <option value="Europe/Paris"      {{ old('timezone', $company->timezone) === 'Europe/Paris'      ? 'selected' : '' }}>Europe/Paris (UTC+1/+2)</option>
                            <option value="UTC"               {{ old('timezone', $company->timezone) === 'UTC'               ? 'selected' : '' }}>UTC</option>
                        </select>
                        @error('timezone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Enregistrer les paramètres
                    </button>
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
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
