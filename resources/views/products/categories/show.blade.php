@extends('layouts.admin')

@section('title', $category->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Catégories</a></li>
    <li class="breadcrumb-item active">{{ $category->name }}</li>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">{{ $category->name }}</h1>
            @if($category->parent)
                <p class="text-muted mb-0">
                    <i class="fas fa-level-up-alt fa-rotate-90 me-1"></i>
                    <a href="{{ route('categories.show', $category->parent) }}">{{ $category->parent->name }}</a>
                    &rsaquo; {{ $category->name }}
                </p>
            @else
                <p class="text-muted mb-0">Catégorie principale</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row g-4">
    <!-- Colonne principale -->
    <div class="col-lg-8">
        <!-- Informations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Nom</dt>
                    <dd class="col-sm-8">{{ $category->name }}</dd>

                    <dt class="col-sm-4">Slug</dt>
                    <dd class="col-sm-8"><code>{{ $category->slug }}</code></dd>

                    @if($category->description)
                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $category->description }}</dd>
                    @endif

                    @if($category->parent)
                        <dt class="col-sm-4">Catégorie parente</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('categories.show', $category->parent) }}">{{ $category->parent->name }}</a>
                        </dd>
                    @endif

                    <dt class="col-sm-4">Statut</dt>
                    <dd class="col-sm-8">
                        @if($category->is_active)
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Actif</span>
                        @else
                            <span class="badge bg-secondary"><i class="fas fa-times me-1"></i>Inactif</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Visible en ligne</dt>
                    <dd class="col-sm-8">
                        @if($category->is_visible_online)
                            <span class="badge bg-info"><i class="fas fa-globe me-1"></i>Oui</span>
                        @else
                            <span class="badge bg-light text-muted"><i class="fas fa-eye-slash me-1"></i>Non</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Créée le</dt>
                    <dd class="col-sm-8">{{ $category->created_at->format('d/m/Y à H:i') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Sous-catégories -->
        @if($category->children->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-sitemap me-2 text-primary"></i>Sous-catégories</h5>
                    <span class="badge bg-secondary">{{ $category->children->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($category->children as $child)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="{{ route('categories.show', $child) }}" class="fw-semibold text-dark">
                                        <i class="fas fa-folder me-2 text-muted"></i>{{ $child->name }}
                                    </a>
                                    <div class="text-muted small">{{ $child->products->count() }} produit(s)</div>
                                </div>
                                <div class="d-flex gap-2 align-items-center">
                                    @if($child->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                    <a href="{{ route('categories.edit', $child) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Produits -->
        @if($category->products->isNotEmpty())
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-box me-2 text-primary"></i>Produits</h5>
                    <span class="badge bg-secondary">{{ $category->products->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th class="text-end">Prix</th>
                                    <th class="text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->products as $product)
                                    <tr>
                                        <td>
                                            <a href="{{ route('products.show', $product) }}" class="fw-semibold text-dark">
                                                {{ $product->name }}
                                            </a>
                                            @if($product->reference)
                                                <div class="text-muted small">Réf: {{ $product->reference }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($product->price, 2, ',', ' ') }} €
                                        </td>
                                        <td class="text-center">
                                            @if($product->is_active)
                                                <span class="badge bg-success">Actif</span>
                                            @else
                                                <span class="badge bg-secondary">Inactif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Colonne latérale -->
    <div class="col-lg-4">
        <!-- Image -->
        @if($category->image_path)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-image me-2 text-primary"></i>Image</h5>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $category->image_url }}" alt="{{ $category->name }}"
                         class="img-fluid rounded" style="max-height: 250px;">
                </div>
            </div>
        @endif

        <!-- SEO -->
        @if($category->meta_title || $category->meta_description)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-search me-2 text-primary"></i>SEO</h5>
                </div>
                <div class="card-body">
                    @if($category->meta_title)
                        <div class="mb-2">
                            <div class="text-muted small">Titre</div>
                            <div>{{ $category->meta_title }}</div>
                        </div>
                    @endif
                    @if($category->meta_description)
                        <div class="mb-0">
                            <div class="text-muted small">Description</div>
                            <div>{{ $category->meta_description }}</div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Statistiques</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Produits directs</span>
                    <strong>{{ $category->products->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Sous-catégories</span>
                    <strong>{{ $category->children->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total produits</span>
                    <strong>{{ $category->getAllProductsCount() }}</strong>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Actions</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                <a href="{{ route('categories.create') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus me-2"></i>Créer une sous-catégorie
                </a>
                <form action="{{ route('categories.toggle-status', $category) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-{{ $category->is_active ? 'warning' : 'success' }} btn-sm w-100">
                        <i class="fas fa-{{ $category->is_active ? 'pause' : 'play' }} me-2"></i>
                        {{ $category->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
