@extends('layouts.admin')

@section('title', 'Produits')

@section('breadcrumb')
<li class="breadcrumb-item active">Produits</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Produits</h1>
        <p class="text-muted mb-0">{{ $products->total() }} produit(s) au total</p>
    </div>
    <div class="d-flex gap-2">
        @can('products.create')
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau produit
        </a>
        @endcan
    </div>
</div>
@endsection

@section('content')
{{-- Filters Card --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom, SKU..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="category">
                    <option value="">Toutes catégories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">Tous types</option>
                    <option value="product" {{ request('type') == 'product' ? 'selected' : '' }}>Produit</option>
                    <option value="service" {{ request('type') == 'service' ? 'selected' : '' }}>Service</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">Tous statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                    <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Stock faible</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-secondary flex-grow-1">
                        <i class="fas fa-filter me-1"></i>Filtrer
                    </button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;"></th>
                        <th>Produit</th>
                        <th>SKU</th>
                        <th>Catégorie</th>
                        <th class="text-center">Type</th>
                        <th class="text-end">Prix</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Statut</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            @if($product->main_image_path)
                                <img src="{{ Storage::url($product->main_image_path) }}" alt="{{ $product->name }}" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-image text-muted"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-decoration-none">
                                {{ $product->name }}
                            </a>
                            @if($product->is_published_online)
                                <i class="fas fa-globe text-success ms-1" title="Publié en ligne"></i>
                            @endif
                        </td>
                        <td><code>{{ $product->sku ?? '-' }}</code></td>
                        <td>
                            @if($product->category)
                                <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product->type === 'service' ? 'info' : 'warning' }}">
                                {{ $product->type === 'service' ? 'Service' : 'Produit' }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">
                            {{ number_format($product->price, 0, ',', ' ') }} <small class="text-muted">FCFA</small>
                        </td>
                        <td class="text-center">
                            @if($product->track_inventory)
                                @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger">Épuisé</span>
                                @elseif($product->stock_quantity <= $product->stock_alert_threshold)
                                    <span class="badge bg-warning">{{ $product->stock_quantity }}</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }}</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                {{ $product->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle table-dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('products.show', $product) }}">
                                            <i class="fas fa-eye me-2 text-muted"></i>Voir
                                        </a>
                                    </li>
                                    @can('products.edit')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('products.edit', $product) }}">
                                            <i class="fas fa-edit me-2 text-muted"></i>Modifier
                                        </a>
                                    </li>
                                    @endcan
                                    <li>
                                        <a class="dropdown-item" href="{{ route('products.duplicate', $product) }}">
                                            <i class="fas fa-copy me-2 text-muted"></i>Dupliquer
                                        </a>
                                    </li>
                                    @can('products.delete')
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fas fa-trash me-2"></i>Supprimer
                                            </button>
                                        </form>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">Aucun produit trouvé</p>
                            @can('products.create')
                            <a href="{{ route('products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Créer le premier produit
                            </a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($products->hasPages())
    <div class="card-footer bg-transparent">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table-dropdown-toggle').forEach(function (el) {
        new bootstrap.Dropdown(el, {
            popperConfig: function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            }
        });
    });
});
</script>
@endpush
