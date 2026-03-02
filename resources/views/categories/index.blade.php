@extends('layouts.admin')

@section('title', 'Catégories')

@section('breadcrumb')
<li class="breadcrumb-item active">Catégories</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Catégories</h1>
        <p class="text-muted mb-0">Organisez vos produits par catégories</p>
    </div>
    @can('categories.manage')
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
        <i class="fas fa-plus me-2"></i>Nouvelle catégorie
    </button>
    @endcan
</div>
@endsection

@section('content')
{{-- Stats Cards --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $categories->count() }}</h3>
                    <p class="text-muted mb-0">Catégories</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="fas fa-box"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $categories->sum('products_count') }}</h3>
                    <p class="text-muted mb-0">Produits catégorisés</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $categories->where('products_count', 0)->count() }}</h3>
                    <p class="text-muted mb-0">Catégories vides</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Categories Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;"></th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th class="text-center">Produits</th>
                        <th>Créée le</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td>
                            <div class="avatar avatar-sm bg-{{ $category->color ?? 'primary' }} bg-opacity-10">
                                <i class="fas fa-{{ $category->icon ?? 'tag' }} text-{{ $category->color ?? 'primary' }}"></i>
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $category->name }}</span>
                            @if($category->slug)
                                <br><small class="text-muted">{{ $category->slug }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted">{{ Str::limit($category->description, 60) ?? '-' }}</span>
                        </td>
                        <td class="text-center">
                            @if($category->products_count > 0)
                                <a href="{{ route('products.index', ['category' => $category->id]) }}" class="badge bg-primary text-decoration-none">
                                    {{ $category->products_count }} produit(s)
                                </a>
                            @else
                                <span class="badge bg-light text-muted">Aucun</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $category->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td>
                            @can('categories.manage')
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="editCategory({{ json_encode($category) }})" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($category->products_count == 0)
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button type="button" class="btn btn-outline-secondary" disabled title="Catégorie utilisée">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucune catégorie créée</p>
                            @can('categories.manage')
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
                                <i class="fas fa-plus me-2"></i>Créer la première catégorie
                            </button>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Category Modal --}}
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="categoryForm" method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div id="methodField"></div>
                
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalLabel">
                        <i class="fas fa-tag me-2"></i>Nouvelle catégorie
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                
                <div class="modal-body">
                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Nom de la catégorie <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="name" required maxlength="100">
                        <div class="invalid-feedback">Le nom est requis</div>
                    </div>
                    
                    {{-- Description --}}
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3" maxlength="500"></textarea>
                        <div class="form-text">Maximum 500 caractères</div>
                    </div>
                    
                    {{-- Color --}}
                    <div class="mb-3">
                        <label class="form-label">Couleur</label>
                        <div class="d-flex flex-wrap gap-2" id="colorPicker">
                            @foreach(['primary', 'success', 'danger', 'warning', 'info', 'secondary'] as $color)
                            <div class="form-check">
                                <input class="form-check-input visually-hidden" type="radio" name="color" id="color_{{ $color }}" value="{{ $color }}" {{ $color === 'primary' ? 'checked' : '' }}>
                                <label class="btn btn-sm btn-{{ $color }} color-option" for="color_{{ $color }}" style="width: 36px; height: 36px; border-radius: 50%;">
                                    <i class="fas fa-check text-white d-none"></i>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Icon --}}
                    <div class="mb-0">
                        <label class="form-label">Icône</label>
                        <div class="d-flex flex-wrap gap-2" id="iconPicker">
                            @foreach(['tag', 'box', 'tshirt', 'laptop', 'utensils', 'car', 'home', 'tools', 'book', 'gift', 'heart', 'star'] as $icon)
                            <div class="form-check">
                                <input class="form-check-input visually-hidden" type="radio" name="icon" id="icon_{{ $icon }}" value="{{ $icon }}" {{ $icon === 'tag' ? 'checked' : '' }}>
                                <label class="btn btn-sm btn-outline-secondary icon-option" for="icon_{{ $icon }}" style="width: 40px; height: 40px;">
                                    <i class="fas fa-{{ $icon }}"></i>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.color-option {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}
.color-option:hover {
    transform: scale(1.1);
}
input[type="radio"]:checked + .color-option i {
    display: block !important;
}
input[type="radio"]:checked + .icon-option {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
}
.icon-option {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
</style>
@endpush

@push('scripts')
<script>
const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
const categoryForm = document.getElementById('categoryForm');
const modalTitle = document.getElementById('categoryModalLabel');
const methodField = document.getElementById('methodField');
const submitBtn = document.getElementById('submitBtn');

// Reset form for new category
function resetForm() {
    categoryForm.action = '{{ route("categories.store") }}';
    categoryForm.reset();
    modalTitle.innerHTML = '<i class="fas fa-tag me-2"></i>Nouvelle catégorie';
    methodField.innerHTML = '';
    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer';
    
    // Reset color selection
    document.getElementById('color_primary').checked = true;
    // Reset icon selection
    document.getElementById('icon_tag').checked = true;
}

// Edit category
function editCategory(category) {
    categoryForm.action = '/categories/' + category.id;
    methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';
    modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>Modifier la catégorie';
    submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Mettre à jour';
    
    document.getElementById('categoryName').value = category.name || '';
    document.getElementById('categoryDescription').value = category.description || '';
    
    // Set color
    const colorInput = document.getElementById('color_' + (category.color || 'primary'));
    if (colorInput) colorInput.checked = true;
    
    // Set icon
    const iconInput = document.getElementById('icon_' + (category.icon || 'tag'));
    if (iconInput) iconInput.checked = true;
    
    categoryModal.show();
}

// Reset form when modal is closed
document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
    resetForm();
});
</script>
@endpush
