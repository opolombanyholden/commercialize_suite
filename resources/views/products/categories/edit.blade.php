@extends('layouts.admin')

@section('title', 'Modifier la catégorie')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Catégories</a></li>
    <li class="breadcrumb-item active">{{ $category->name }}</li>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Modifier la catégorie</h1>
            <p class="text-muted mb-0">{{ $category->name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.show', $category) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-2"></i>Voir
            </a>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
@endsection

@section('content')
<form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

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
                               id="name" name="name" value="{{ old('name', $category->name) }}"
                               required autofocus>
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
                                <option value="{{ $parent->id }}"
                                    {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="3">{{ old('description', $category->description) }}</textarea>
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
                               id="meta_title" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}">
                        @error('meta_title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-0">
                        <label for="meta_description" class="form-label">Description SEO</label>
                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                  id="meta_description" name="meta_description" rows="2">{{ old('meta_description', $category->meta_description) }}</textarea>
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
                    @if($category->image_path)
                        <div id="currentImageContainer" class="mb-3">
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                                 class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                        <div class="form-check mb-3 text-start">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                            <label class="form-check-label text-danger" for="remove_image">
                                Supprimer l'image actuelle
                            </label>
                        </div>
                    @else
                        <div id="imagePlaceholder" class="border rounded d-flex align-items-center justify-content-center bg-light mb-3" style="height: 150px;">
                            <div class="text-muted">
                                <i class="fas fa-image fa-2x mb-2"></i>
                                <div>Aucune image</div>
                            </div>
                        </div>
                    @endif
                    <div id="imagePreviewContainer" class="mb-3" style="{{ $category->image_path ? 'display:none' : 'display:none' }}">
                        <img id="imagePreview" src="" alt="Aperçu" class="img-fluid rounded" style="max-height: 200px;">
                    </div>
                    <label class="form-label">Nouvelle image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror"
                           id="image" name="image" accept="image/*">
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
                               value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Catégorie active</label>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_visible_online" name="is_visible_online"
                               value="1" {{ old('is_visible_online', $category->is_visible_online) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_visible_online">Visible en ligne (boutique)</label>
                    </div>
                </div>
            </div>

            <!-- Danger zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger bg-opacity-10">
                    <h5 class="card-title mb-0 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Zone de danger</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">La suppression est irréversible et impossible si la catégorie contient des produits.</p>
                    <button type="button" class="btn btn-outline-danger btn-sm w-100"
                            onclick="confirmDelete('delete-category-form', 'Supprimer la catégorie « {{ addslashes($category->name) }} » ?')">
                        <i class="fas fa-trash me-2"></i>Supprimer cette catégorie
                    </button>
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
                </button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                    Annuler
                </a>
            </div>
        </div>
    </div>
</form>

{{-- Formulaire de suppression HORS du formulaire principal --}}
<form action="{{ route('categories.destroy', $category) }}" method="POST" id="delete-category-form" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
document.getElementById('image').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');

    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
    }
});
</script>
@endpush
