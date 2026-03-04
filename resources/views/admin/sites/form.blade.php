{{-- Site Form Partial --}}

<form action="{{ isset($site) ? route('admin.sites.update', $site) : route('admin.sites.store') }}" method="POST">
    @csrf
    @if(isset($site))
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-8">

            {{-- Informations générales --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Informations du site</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nom du site <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', $site->name ?? '') }}"
                                   placeholder="Ex: Boutique Libreville Centre" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="code" class="form-label">Code</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror"
                                   id="code" name="code"
                                   value="{{ old('code', $site->code ?? '') }}"
                                   placeholder="Ex: LBV-001">
                            <div class="form-text">Identifiant court unique (facultatif)</div>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone"
                                   value="{{ old('phone', $site->phone ?? '') }}"
                                   placeholder="+241 01 23 45 67">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email', $site->email ?? '') }}"
                                   placeholder="site@entreprise.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="2"
                                      placeholder="Description courte du site">{{ old('description', $site->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Adresse --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-location-arrow me-2 text-primary"></i>Adresse</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Adresse</label>
                            <textarea class="form-control @error('address') is-invalid @enderror"
                                      id="address" name="address" rows="2"
                                      placeholder="Rue, quartier...">{{ old('address', $site->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="city" class="form-label">Ville</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror"
                                   id="city" name="city"
                                   value="{{ old('city', $site->city ?? '') }}"
                                   placeholder="Libreville">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="country" class="form-label">Pays</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror"
                                   id="country" name="country"
                                   value="{{ old('country', $site->country ?? 'Gabon') }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column --}}
        <div class="col-lg-4">

            {{-- Paramètres --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Paramètres</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_headquarters" name="is_headquarters"
                               value="1" {{ old('is_headquarters', $site->is_headquarters ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_headquarters">
                            <i class="fas fa-star text-warning me-1"></i>Siège principal
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_store" name="is_store"
                               value="1" {{ old('is_store', $site->is_store ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_store">
                            <i class="fas fa-store text-info me-1"></i>Point de vente
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_warehouse" name="is_warehouse"
                               value="1" {{ old('is_warehouse', $site->is_warehouse ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_warehouse">
                            <i class="fas fa-warehouse text-secondary me-1"></i>Entrepôt / stock
                        </label>
                    </div>

                    <hr>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" {{ old('is_active', $site->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Site actif</label>
                    </div>
                </div>
            </div>

            {{-- Responsable --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Responsable</h5>
                </div>
                <div class="card-body">
                    <label for="manager_id" class="form-label">Responsable du site</label>
                    <select class="form-select @error('manager_id') is-invalid @enderror" id="manager_id" name="manager_id">
                        <option value="">-- Aucun --</option>
                        @foreach($managers ?? [] as $manager)
                            <option value="{{ $manager->id }}"
                                {{ old('manager_id', $site->manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                                {{ $manager->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('manager_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>{{ isset($site) ? 'Mettre à jour' : 'Créer le site' }}
                        </button>
                        <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>
