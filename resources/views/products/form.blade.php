{{-- Product Form Partial - Used by create.blade.php and edit.blade.php --}}

<form action="{{ isset($product) ? route('products.update', $product) : route('products.store') }}" method="POST" enctype="multipart/form-data" id="productForm">
    @csrf
    @if(isset($product))
        @method('PUT')
    @endif
    
    <div class="row">
        {{-- Main Content --}}
        <div class="col-lg-8">
            {{-- General Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Name --}}
                        <div class="col-md-8">
                            <label for="name" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name ?? '') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- SKU --}}
                        <div class="col-md-4">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}" placeholder="Auto-généré si vide">
                            @error('sku')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Category --}}
                        <div class="col-md-6">
                            <label for="category_id" class="form-label">Catégorie</label>
                            <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id">
                                <option value="">-- Aucune catégorie --</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Type --}}
                        <div class="col-md-6">
                            <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="product" {{ old('type', $product->type ?? 'product') == 'product' ? 'selected' : '' }}>Produit physique</option>
                                <option value="service" {{ old('type', $product->type ?? '') == 'service' ? 'selected' : '' }}>Service</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Short Description --}}
                        <div class="col-12">
                            <label for="short_description" class="form-label">Description courte</label>
                            <input type="text" class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" value="{{ old('short_description', $product->short_description ?? '') }}" maxlength="255">
                            <div class="form-text">Maximum 255 caractères</div>
                            @error('short_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Full Description --}}
                        <div class="col-12">
                            <label for="description" class="form-label">Description complète</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $product->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Pricing --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tags me-2 text-primary"></i>Prix et taxes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- Price --}}
                        <div class="col-md-4">
                            <label for="price" class="form-label">Prix de vente <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price ?? '') }}" step="0.01" min="0" required>
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Cost Price --}}
                        <div class="col-md-4">
                            <label for="cost_price" class="form-label">Prix de revient</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? '') }}" step="0.01" min="0">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            @error('cost_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Compare at Price --}}
                        <div class="col-md-4">
                            <label for="compare_at_price" class="form-label">Prix barré</label>
                            <div class="input-group">
                                <input type="number" class="form-control @error('compare_at_price') is-invalid @enderror" id="compare_at_price" name="compare_at_price" value="{{ old('compare_at_price', $product->compare_at_price ?? '') }}" step="0.01" min="0">
                                <span class="input-group-text">FCFA</span>
                            </div>
                            <div class="form-text">Pour afficher une promotion</div>
                            @error('compare_at_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Tax --}}
                        <div class="col-md-6">
                            <label for="tax_id" class="form-label">Taxe applicable</label>
                            <select class="form-select @error('tax_id') is-invalid @enderror" id="tax_id" name="tax_id">
                                <option value="">-- Aucune taxe --</option>
                                @foreach($taxes ?? [] as $tax)
                                    <option value="{{ $tax->id }}" {{ old('tax_id', $product->tax_id ?? '') == $tax->id ? 'selected' : '' }}>
                                        {{ $tax->name }} ({{ $tax->rate }}%)
                                    </option>
                                @endforeach
                            </select>
                            @error('tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Inventory --}}
            <div class="card border-0 shadow-sm mb-4" id="inventoryCard">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-warehouse me-2 text-primary"></i>Inventaire
                    </h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="track_inventory" value="0">
                        <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" value="1" {{ old('track_inventory', $product->track_inventory ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="track_inventory">Suivre le stock de ce produit</label>
                    </div>
                    
                    <div class="row g-3" id="inventoryFields">
                        {{-- Stock Quantity --}}
                        <div class="col-md-4">
                            <label for="stock_quantity" class="form-label">Quantité en stock</label>
                            <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}" min="0" step="1">
                            @error('stock_quantity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Low Stock Threshold --}}
                        <div class="col-md-4">
                            <label for="stock_alert_threshold" class="form-label">Seuil d'alerte</label>
                            <input type="number" class="form-control @error('stock_alert_threshold') is-invalid @enderror" id="stock_alert_threshold" name="stock_alert_threshold" value="{{ old('stock_alert_threshold', $product->stock_alert_threshold ?? 5) }}" min="0" step="1">
                            <div class="form-text">Alerte si stock ≤ ce seuil</div>
                            @error('stock_alert_threshold')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        {{-- Barcode --}}
                        <div class="col-md-4">
                            <label for="barcode" class="form-label">Code-barres</label>
                            <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}">
                            @error('barcode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Galerie d'images --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-images me-2 text-primary"></i>Galerie d'images
                    </h5>
                    @isset($product)
                        <span class="badge bg-secondary">{{ $product->images->count() }} image(s)</span>
                    @endisset
                </div>
                <div class="card-body">
                    {{-- Images existantes (mode édition) --}}
                    @isset($product)
                        @if($product->images->count() > 0)
                            <div class="row g-2 mb-3">
                                @foreach($product->images as $image)
                                    <div class="col-4 col-md-3">
                                        <div class="position-relative border rounded overflow-hidden" style="aspect-ratio: 1;">
                                            <img src="{{ Storage::url($image->image_path) }}"
                                                 alt="{{ $image->alt_text ?? '' }}"
                                                 class="w-100 h-100" style="object-fit: cover;">
                                            @if($image->is_primary)
                                                <span class="position-absolute top-0 start-0 m-1">
                                                    <span class="badge bg-primary"><i class="fas fa-star"></i></span>
                                                </span>
                                            @endif
                                            <div class="position-absolute bottom-0 start-0 end-0 p-1 d-flex gap-1"
                                                 style="background: rgba(0,0,0,0.55);">
                                                @if(!$image->is_primary)
                                                    <form action="{{ route('products.images.primary', [$product, $image]) }}" method="POST" class="flex-fill">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning w-100" title="Définir comme principale">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('products.images.delete', [$product, $image]) }}" method="POST" class="flex-fill">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger w-100"
                                                            onclick="return confirm('Supprimer cette image ?')"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-3">Aucune image dans la galerie.</p>
                        @endif
                    @endisset

                    {{-- Upload de nouvelles images --}}
                    <label for="gallery_images" class="form-label">
                        Ajouter des images
                        <small class="text-muted fw-normal">(JPG, PNG, GIF — max 5 Mo / image)</small>
                    </label>
                    <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                           id="gallery_images" name="images[]" accept="image/*" multiple>
                    @error('images.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    {{-- Prévisualisation des nouvelles images --}}
                    <div id="galleryPreview" class="row g-2 mt-2"></div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            {{-- Status --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Statut</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Produit actif</label>
                    </div>
                    
                    @feature('ecommerce')
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_published_online" value="0">
                        <input class="form-check-input" type="checkbox" id="is_published_online" name="is_published_online" value="1" {{ old('is_published_online', $product->is_published_online ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published_online">
                            <i class="fas fa-globe me-1"></i>Publier sur la boutique en ligne
                        </label>
                    </div>
                    @endfeature
                </div>
            </div>
            
            {{-- Image --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0">Image principale</h5>
                </div>
                <div class="card-body">
                    <div id="imagePreview" class="mb-3 text-center">
                        @if(isset($product) && $product->main_image_path)
                            <img src="{{ Storage::url($product->main_image_path) }}" alt="Image produit" class="img-fluid rounded" style="max-height: 200px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <input type="file" class="form-control @error('main_image') is-invalid @enderror" id="main_image" name="main_image" accept="image/*">
                    <div class="form-text">JPG, PNG ou GIF. Max 2 Mo.</div>
                    @error('main_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            {{-- Actions --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i>{{ isset($product) ? 'Mettre à jour' : 'Créer le produit' }}
                        </button>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const inventoryCard = document.getElementById('inventoryCard');

    // Hide inventory card for services only
    function toggleInventoryCard() {
        inventoryCard.style.display = typeSelect.value === 'service' ? 'none' : '';
    }

    typeSelect.addEventListener('change', toggleInventoryCard);
    toggleInventoryCard();
    
    // Image preview
    const imageInput = document.getElementById('main_image');
    const imagePreview = document.getElementById('imagePreview');
    
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">';
            };
            reader.readAsDataURL(file);
        }
    });

    // Gallery preview
    const galleryInput = document.getElementById('gallery_images');
    const galleryPreview = document.getElementById('galleryPreview');

    galleryInput.addEventListener('change', function() {
        galleryPreview.innerHTML = '';
        Array.from(this.files).forEach(function(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-4 col-md-3';
                col.innerHTML =
                    '<div class="position-relative border rounded overflow-hidden" style="aspect-ratio:1;">' +
                        '<img src="' + e.target.result + '" class="w-100 h-100" style="object-fit:cover;">' +
                        '<span class="position-absolute top-0 end-0 m-1">' +
                            '<span class="badge bg-info" title="En attente d\'envoi"><i class="fas fa-clock"></i></span>' +
                        '</span>' +
                    '</div>';
                galleryPreview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });
});
</script>
@endpush
