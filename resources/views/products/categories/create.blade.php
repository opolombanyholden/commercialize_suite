@extends('layouts.admin')

@section('title', 'Nouvelle catégorie')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Catégories</a></li>
    <li class="breadcrumb-item active">Nouvelle catégorie</li>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Nouvelle catégorie</h1>
            <p class="text-muted mb-0">Créer une nouvelle catégorie de produits</p>
        </div>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
@endsection

@section('content')
<form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="row g-4">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}"
                               placeholder="Ex: Électronique, Vêtements..." required autofocus>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label">Catégorie parente</label>
                        <select class="form-select @error('parent_id') is-invalid @enderror"
                                id="parent_id" name="parent_id">
                            <option value="">— Catégorie racine —</option>
                            @foreach($parentCategories as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Laissez vide pour créer une catégorie principale.</div>
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3"
                                  placeholder="Description de la catégorie (optionnel)">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- SEO -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-search me-2 text-primary"></i>Référencement (SEO)</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="meta_title" class="form-label">Titre SEO</label>
                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                               id="meta_title" name="meta_title" value="{{ old('meta_title') }}"
                               placeholder="Titre pour les moteurs de recherche">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="meta_description" class="form-label">Description SEO</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description" name="meta_description" rows="2"
                                  placeholder="Description pour les moteurs de recherche (max. 160 caractères)">{{ old('meta_description') }}</textarea>
                        @error('meta_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="col-lg-4">
            <!-- Image -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-image me-2 text-primary"></i>Image</h5>
                </div>
                <div class="card-body text-center">
                    <div id="imagePreviewContainer" class="mb-3" style="display: none;">
                        <img id="imagePreview" src="" alt="Aperçu" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <div id="imagePlaceholder" class="border rounded d-flex align-items-center justify-content-center bg-light mb-3" style="height: 150px;">
                        <div class="text-muted">
                            <i class="fas fa-image fa-2x mb-2"></i>
                            <div>Aucune image</div>
                        </div>
                    </div>
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                           id="image" name="image" accept="image/*" data-preview="imagePreview">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">JPG, PNG, GIF. Max 2 Mo.</div>
                </div>
            </div>

            <!-- Options -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-cog me-2 text-primary"></i>Options</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                               value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Catégorie active</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_visible_online" name="is_visible_online"
                               value="1" {{ old('is_visible_online') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_visible_online">Visible en ligne (boutique)</label>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Créer la catégorie
                </button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');
    const placeholder = document.getElementById('imagePlaceholder');

    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
            placeholder.style.display = 'none';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endpush
