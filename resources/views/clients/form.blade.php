{{-- Client Form Partial --}}

<form action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}" method="POST" id="clientForm">
    @csrf
    @if(isset($client))
        @method('PUT')
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            {{-- General Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Type --}}
                        <div class="col-12">
                            <label class="form-label">Type de client <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="type" id="type_individual" value="individual" {{ old('type', $client->type ?? 'individual') == 'individual' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="type_individual">
                                    <i class="fas fa-user me-2"></i>Particulier
                                </label>
                                
                                <input type="radio" class="btn-check" name="type" id="type_company" value="business" {{ old('type', $client->type ?? '') == 'business' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="type_company">
                                    <i class="fas fa-building me-2"></i>Entreprise
                                </label>
                            </div>
                        </div>
                        
                        {{-- Name --}}
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom complet / Raison sociale <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $client->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Company Name (for individuals) --}}
                        <div class="col-md-6" id="companyNameField">
                            <label for="company_name" class="form-label">Société (optionnel)</label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $client->company_name ?? '') }}">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Email --}}
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $client->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $client->phone ?? '') }}" placeholder="+241 XX XX XX XX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Tax ID --}}
                        <div class="col-md-6" id="taxIdField">
                            <label for="tax_id" class="form-label">N° Contribuable / NIF</label>
                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" id="tax_id" name="tax_id" value="{{ old('tax_id', $client->tax_id ?? '') }}">
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Address --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt me-2 text-primary"></i>Adresse
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Address --}}
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $client->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- City --}}
                        <div class="col-md-4">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $client->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Postal Code --}}
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">Code postal</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $client->postal_code ?? '') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Country --}}
                        <div class="col-md-4">
                            <label for="country" class="form-label">Pays</label>
                            <select class="form-select @error('country') is-invalid @enderror" id="country" name="country">
                                <option value="Gabon" {{ old('country', $client->country ?? 'Gabon') == 'Gabon' ? 'selected' : '' }}>Gabon</option>
                                <option value="Cameroun" {{ old('country', $client->country ?? '') == 'Cameroun' ? 'selected' : '' }}>Cameroun</option>
                                <option value="Congo" {{ old('country', $client->country ?? '') == 'Congo' ? 'selected' : '' }}>Congo</option>
                                <option value="Guinée équatoriale" {{ old('country', $client->country ?? '') == 'Guinée équatoriale' ? 'selected' : '' }}>Guinée équatoriale</option>
                                <option value="France" {{ old('country', $client->country ?? '') == 'France' ? 'selected' : '' }}>France</option>
                                <option value="Autre" {{ old('country', $client->country ?? '') == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Notes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sticky-note me-2 text-primary"></i>Notes internes
                    </h5>
                </div>
                <div class="card-body">
                    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3" placeholder="Notes privées sur ce client...">{{ old('notes', $client->notes ?? '') }}</textarea>
                    <div class="form-text">Ces notes ne sont visibles que par votre équipe.</div>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Actions --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>{{ isset($client) ? 'Mettre à jour' : 'Créer le client' }}
                        </button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
            
            {{-- Quick Info --}}
            @if(isset($client))
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Statistiques</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Factures</td>
                            <td class="text-end fw-semibold">{{ $client->invoices_count ?? 0 }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">CA total</td>
                            <td class="text-end fw-semibold">{{ number_format($client->total_revenue ?? 0, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Créé le</td>
                            <td class="text-end">{{ $client->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const companyNameField = document.getElementById('companyNameField');
    const taxIdField = document.getElementById('taxIdField');
    
    function toggleFields() {
        const isCompany = document.getElementById('type_company').checked;
        companyNameField.style.display = isCompany ? 'none' : '';
    }
    
    typeRadios.forEach(radio => {
        radio.addEventListener('change', toggleFields);
    });
    
    toggleFields();
});
</script>
@endpush
