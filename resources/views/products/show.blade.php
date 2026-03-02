@extends('layouts.admin')

@section('title', $product->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
<li class="breadcrumb-item active">{{ Str::limit($product->name, 40) }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="page-title mb-1">{{ $product->name }}</h1>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            @if($product->sku)
                <code class="text-muted">{{ $product->sku }}</code>
            @endif
            <span class="badge bg-{{ $product->type === 'service' ? 'info' : 'warning' }}">
                {{ $product->type === 'service' ? 'Service' : 'Produit physique' }}
            </span>
            @if($product->category)
                <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
            @endif
            <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                {{ $product->is_active ? 'Actif' : 'Inactif' }}
            </span>
            @if($product->is_published_online)
                <span class="badge bg-primary"><i class="fas fa-globe me-1"></i>En ligne</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        @can('products.edit')
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
            <i class="fas fa-edit me-2"></i>Modifier
        </a>
        @endcan
        <a href="{{ route('products.duplicate', $product) }}" class="btn btn-outline-secondary">
            <i class="fas fa-copy me-2"></i>Dupliquer
        </a>
    </div>
</div>
@endsection

@section('content')

{{-- ===== KPI CARDS ===== --}}
@php
    $revenueEstimated = $product->sales_count * $product->price;
    $marginUnit = $product->cost_price ? ($product->price - $product->cost_price) : null;
    $marginPct  = ($marginUnit !== null && $product->price > 0) ? ($marginUnit / $product->price * 100) : null;
@endphp
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Unités vendues</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['total_sold']) }}</h3>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">CA estimé</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($revenueEstimated, 0, ',', ' ') }}</h3>
                        <small class="text-muted">FCFA</small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Vues produit</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($stats['views']) }}</h3>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Marge unitaire</p>
                        @if($marginUnit !== null)
                            <h3 class="mb-0 fw-bold text-{{ $marginUnit >= 0 ? 'success' : 'danger' }}">
                                {{ number_format($marginUnit, 0, ',', ' ') }}
                            </h3>
                            <small class="text-muted">FCFA ({{ number_format($marginPct, 1) }}%)</small>
                        @else
                            <h3 class="mb-0 fw-bold text-muted">—</h3>
                            <small class="text-muted">Prix de revient manquant</small>
                        @endif
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- ===== COLONNE PRINCIPALE ===== --}}
    <div class="col-lg-8">

        {{-- Fiche produit --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-4 mb-md-0">
                        @if($product->main_image_path)
                            <img src="{{ Storage::url($product->main_image_path) }}" alt="{{ $product->name }}"
                                 class="img-fluid rounded" style="object-fit:cover; width:100%; max-height:220px;">
                        @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 220px;">
                                <i class="fas fa-image fa-4x text-muted"></i>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h2 class="mb-1">{{ number_format($product->price, 0, ',', ' ') }} <small class="text-muted fs-6">FCFA</small></h2>
                        @if($product->compare_at_price && $product->compare_at_price > $product->price)
                            @php $discount = round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100); @endphp
                            <p class="mb-2">
                                <del class="text-muted">{{ number_format($product->compare_at_price, 0, ',', ' ') }} FCFA</del>
                                <span class="badge bg-danger ms-1">-{{ $discount }}%</span>
                            </p>
                        @endif

                        @if($product->short_description)
                            <p class="text-muted mb-3">{{ $product->short_description }}</p>
                        @endif

                        @if($product->description)
                            <div class="text-muted small">{!! nl2br(e($product->description)) !!}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== GALERIE D'IMAGES ===== --}}
        @if($product->images->count() > 0)
        @php
            $galleryImages = $product->images->map(fn($img) => [
                'url' => Storage::url($img->image_path),
                'alt' => $img->alt_text ?? $product->name,
                'primary' => $img->is_primary,
            ])->values();
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-images me-2 text-primary"></i>Galerie d'images</h5>
                <span class="badge bg-secondary">{{ $product->images->count() }} image(s)</span>
            </div>
            <div class="card-body">
                <div class="row g-2" id="galleryGrid">
                    @foreach($product->images as $i => $image)
                        <div class="col-4 col-md-3">
                            <div class="position-relative border rounded overflow-hidden gallery-thumb"
                                 style="aspect-ratio: 1; cursor: zoom-in;"
                                 data-index="{{ $i }}"
                                 title="Cliquer pour agrandir">
                                <img src="{{ Storage::url($image->image_path) }}"
                                     alt="{{ $image->alt_text ?? $product->name }}"
                                     class="w-100 h-100" style="object-fit: cover; transition: transform .25s ease;">
                                @if($image->is_primary)
                                    <span class="position-absolute top-0 start-0 m-1">
                                        <span class="badge bg-primary"><i class="fas fa-star me-1"></i>Principale</span>
                                    </span>
                                @endif
                                <div class="gallery-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-search-plus fa-2x text-white"></i>
                                </div>
                            </div>
                            @can('products.edit')
                            <div class="mt-1 d-flex gap-1">
                                @if(!$image->is_primary)
                                    <form action="{{ route('products.images.primary', [$product, $image]) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning w-100" title="Définir comme image principale">
                                            <i class="fas fa-star me-1"></i><small>Principale</small>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('products.images.delete', [$product, $image]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Supprimer cette image ?')"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Modal Lightbox --}}
        <div class="modal fade" id="galleryLightbox" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl">
                <div class="modal-content bg-transparent border-0 shadow-none">
                    <div class="modal-body p-0 position-relative text-center">
                        {{-- Fermer --}}
                        <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3"
                                data-bs-dismiss="modal" style="filter: drop-shadow(0 0 3px #000);"></button>
                        {{-- Image --}}
                        <img id="lightboxImg" src="" alt=""
                             class="img-fluid rounded shadow-lg"
                             style="max-height: 85vh; object-fit: contain; transition: opacity .2s ease;">
                        {{-- Légende --}}
                        <div id="lightboxCaption" class="text-white mt-2 small" style="text-shadow: 0 1px 3px #000;"></div>
                        {{-- Navigation --}}
                        <button id="lightboxPrev" class="btn btn-dark btn-lg rounded-circle position-absolute top-50 start-0 translate-middle-y ms-2 z-3"
                                style="width:48px;height:48px;opacity:.8;">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button id="lightboxNext" class="btn btn-dark btn-lg rounded-circle position-absolute top-50 end-0 translate-middle-y me-2 z-3"
                                style="width:48px;height:48px;opacity:.8;">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ===== STOCK & INVENTAIRE ===== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-warehouse me-2 text-primary"></i>Stock & Inventaire</h5>
                @if($product->track_inventory)
                    @if($product->stock_quantity <= 0)
                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Épuisé</span>
                    @elseif($product->stock_alert_threshold && $product->stock_quantity <= $product->stock_alert_threshold)
                        <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Stock faible</span>
                    @else
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>En stock</span>
                    @endif
                @else
                    <span class="badge bg-secondary">Non suivi</span>
                @endif
            </div>
            <div class="card-body">
                @if($product->track_inventory)
                    <div class="row g-3 mb-3">
                        {{-- Stock actuel --}}
                        <div class="col-sm-4">
                            @php
                                $stockClass = $product->stock_quantity <= 0
                                    ? 'bg-danger bg-opacity-10 border-danger'
                                    : ($product->stock_alert_threshold && $product->stock_quantity <= $product->stock_alert_threshold
                                        ? 'bg-warning bg-opacity-10 border-warning'
                                        : 'bg-success bg-opacity-10 border-success');
                                $textClass = $product->stock_quantity <= 0
                                    ? 'text-danger'
                                    : ($product->stock_alert_threshold && $product->stock_quantity <= $product->stock_alert_threshold
                                        ? 'text-warning'
                                        : 'text-success');
                            @endphp
                            <div class="text-center p-3 rounded border {{ $stockClass }}">
                                <p class="text-muted mb-1 small">Stock actuel</p>
                                <h2 class="mb-0 fw-bold {{ $textClass }}">{{ $product->stock_quantity }}</h2>
                                <small class="text-muted">unités</small>
                            </div>
                        </div>
                        {{-- Seuil d'alerte --}}
                        <div class="col-sm-4">
                            <div class="text-center p-3 bg-light rounded border">
                                <p class="text-muted mb-1 small">Seuil d'alerte</p>
                                <h2 class="mb-0 fw-bold">{{ $product->stock_alert_threshold ?? '—' }}</h2>
                                <small class="text-muted">unités</small>
                            </div>
                        </div>
                        {{-- Valeur du stock --}}
                        <div class="col-sm-4">
                            <div class="text-center p-3 bg-light rounded border">
                                <p class="text-muted mb-1 small">Valeur du stock</p>
                                @php $stockValue = $product->stock_quantity * ($product->cost_price ?? $product->price); @endphp
                                <h2 class="mb-0 fw-bold">{{ number_format($stockValue, 0, ',', ' ') }}</h2>
                                <small class="text-muted">FCFA</small>
                            </div>
                        </div>
                    </div>

                    {{-- Barre de progression du stock --}}
                    @if($product->stock_alert_threshold && $product->stock_alert_threshold > 0)
                        @php
                            $maxStock = max($product->stock_quantity, $product->stock_alert_threshold * 3, 1);
                            $pct = min(100, round($product->stock_quantity / $maxStock * 100));
                            $barClass = $product->stock_quantity <= 0 ? 'bg-danger' :
                                        ($product->stock_quantity <= $product->stock_alert_threshold ? 'bg-warning' : 'bg-success');
                        @endphp
                        <div class="mt-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Niveau de stock</small>
                                <small class="text-muted">{{ $product->stock_quantity }} / {{ $maxStock }}</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $barClass }}" role="progressbar"
                                     style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">0</small>
                                <small class="text-warning">⚠ Seuil : {{ $product->stock_alert_threshold }}</small>
                            </div>
                        </div>
                    @endif

                    @if($product->barcode)
                        <div class="mt-3 pt-3 border-top">
                            <i class="fas fa-barcode me-2 text-muted"></i>
                            <strong>Code-barres :</strong> <code>{{ $product->barcode }}</code>
                        </div>
                    @endif
                @else
                    <div class="text-center py-3 text-muted">
                        <i class="fas fa-toggle-off fa-2x mb-2"></i>
                        <p class="mb-1">Le suivi de stock n'est pas activé pour ce produit.</p>
                        @if($product->barcode)
                            <p class="mb-0"><i class="fas fa-barcode me-1"></i>Code-barres : <code>{{ $product->barcode }}</code></p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- ===== STATISTIQUES DE VENTE ===== --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Statistiques de vente</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    {{-- Unités vendues --}}
                    <div class="col-sm-6 col-xl-3">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <div class="text-primary fs-4"><i class="fas fa-box-open"></i></div>
                            <div>
                                <div class="fw-bold fs-5">{{ number_format($stats['total_sold']) }}</div>
                                <div class="text-muted small">Unités vendues</div>
                            </div>
                        </div>
                    </div>
                    {{-- CA estimé --}}
                    <div class="col-sm-6 col-xl-3">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <div class="text-success fs-4"><i class="fas fa-money-bill-wave"></i></div>
                            <div>
                                <div class="fw-bold fs-5">{{ number_format($revenueEstimated, 0, ',', ' ') }}</div>
                                <div class="text-muted small">CA estimé (FCFA)</div>
                            </div>
                        </div>
                    </div>
                    {{-- Vues --}}
                    <div class="col-sm-6 col-xl-3">
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <div class="text-info fs-4"><i class="fas fa-eye"></i></div>
                            <div>
                                <div class="fw-bold fs-5">{{ number_format($stats['views']) }}</div>
                                <div class="text-muted small">Vues</div>
                            </div>
                        </div>
                    </div>
                    {{-- Taux de conversion --}}
                    <div class="col-sm-6 col-xl-3">
                        @php
                            $convRate = ($stats['views'] > 0)
                                ? round($stats['total_sold'] / $stats['views'] * 100, 1)
                                : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                            <div class="text-warning fs-4"><i class="fas fa-funnel-dollar"></i></div>
                            <div>
                                <div class="fw-bold fs-5">{{ $convRate }}%</div>
                                <div class="text-muted small">Taux conversion</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($stats['total_sold'] > 0 && $marginUnit !== null)
                    <div class="mt-4 pt-3 border-top">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="p-3 rounded border">
                                    <p class="text-muted small mb-1">Bénéfice total estimé</p>
                                    @php $totalProfit = $stats['total_sold'] * $marginUnit; @endphp
                                    <h4 class="mb-0 text-{{ $totalProfit >= 0 ? 'success' : 'danger' }}">
                                        {{ number_format($totalProfit, 0, ',', ' ') }} FCFA
                                    </h4>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-3 rounded border">
                                    <p class="text-muted small mb-1">Coût total des ventes</p>
                                    @php $totalCost = $stats['total_sold'] * $product->cost_price; @endphp
                                    <h4 class="mb-0">{{ number_format($totalCost, 0, ',', ' ') }} FCFA</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if($stats['total_sold'] == 0 && $stats['views'] == 0)
                    <p class="text-muted text-center mt-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Aucune donnée de vente enregistrée pour ce produit.
                    </p>
                @endif
            </div>
        </div>

        {{-- Informations financières --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Informations financières</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <p class="text-muted mb-1 small">Prix de vente</p>
                            <h4 class="mb-0">{{ number_format($product->price, 0, ',', ' ') }}</h4>
                            <small class="text-muted">FCFA</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <p class="text-muted mb-1 small">Prix de revient</p>
                            <h4 class="mb-0">{{ $product->cost_price ? number_format($product->cost_price, 0, ',', ' ') : '—' }}</h4>
                            <small class="text-muted">FCFA</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 bg-light rounded">
                            <p class="text-muted mb-1 small">Marge unitaire</p>
                            @if($marginUnit !== null)
                                <h4 class="mb-0 text-{{ $marginUnit >= 0 ? 'success' : 'danger' }}">
                                    {{ number_format($marginUnit, 0, ',', ' ') }}
                                </h4>
                                <small class="text-muted">FCFA ({{ number_format($marginPct, 1) }}%)</small>
                            @else
                                <h4 class="mb-0">—</h4>
                            @endif
                        </div>
                    </div>
                </div>
                @if($product->tax)
                    <div class="mt-3 pt-3 border-top">
                        <i class="fas fa-percent me-2 text-muted"></i>
                        <strong>Taxe :</strong> {{ $product->tax->name }} ({{ $product->tax->rate }}%)
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== SIDEBAR ===== --}}
    <div class="col-lg-4">
        {{-- Actions --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-bolt me-2 text-primary"></i>Actions</h5>
            </div>
            <div class="card-body d-grid gap-2">
                @can('products.edit')
                <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Modifier
                </a>
                @endcan

                <form action="{{ route('products.toggle-status', $product) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-{{ $product->is_active ? 'warning' : 'success' }} w-100">
                        <i class="fas fa-{{ $product->is_active ? 'pause' : 'play' }} me-2"></i>
                        {{ $product->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </form>

                <a href="{{ route('products.duplicate', $product) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-copy me-2"></i>Dupliquer
                </a>

                @feature('ecommerce')
                @if($product->is_published_online)
                <a href="{{ '#' }}" class="btn btn-outline-success" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>Voir sur la boutique
                </a>
                @endif
                @endfeature

                @can('products.delete')
                <form action="{{ route('products.destroy', $product) }}" method="POST"
                      id="delete-product-form" class="d-none">
                    @csrf @method('DELETE')
                </form>
                <button type="button" class="btn btn-outline-danger"
                        onclick="confirmDelete('delete-product-form', 'Supprimer définitivement « {{ addslashes($product->name) }} » ?')">
                    <i class="fas fa-trash me-2"></i>Supprimer
                </button>
                @endcan
            </div>
        </div>

        {{-- Résumé stock --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-boxes me-2 text-primary"></i>État du stock</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted ps-3">Suivi activé</td>
                        <td class="text-end pe-3">
                            @if($product->track_inventory)
                                <span class="badge bg-success">Oui</span>
                            @else
                                <span class="badge bg-secondary">Non</span>
                            @endif
                        </td>
                    </tr>
                    @if($product->track_inventory)
                    <tr>
                        <td class="text-muted ps-3">Quantité</td>
                        <td class="text-end pe-3 fw-bold">{{ $product->stock_quantity }} unités</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Seuil d'alerte</td>
                        <td class="text-end pe-3">{{ $product->stock_alert_threshold ?? '—' }} unités</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Statut</td>
                        <td class="text-end pe-3">
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger">Épuisé</span>
                            @elseif($product->stock_alert_threshold && $product->stock_quantity <= $product->stock_alert_threshold)
                                <span class="badge bg-warning text-dark">Stock faible</span>
                            @else
                                <span class="badge bg-success">En stock</span>
                            @endif
                        </td>
                    </tr>
                    @if($product->cost_price)
                    <tr>
                        <td class="text-muted ps-3">Valeur stock</td>
                        <td class="text-end pe-3">{{ number_format($product->stock_quantity * $product->cost_price, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endif
                    @endif
                    @if($product->barcode)
                    <tr>
                        <td class="text-muted ps-3">Code-barres</td>
                        <td class="text-end pe-3"><code>{{ $product->barcode }}</code></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Informations produit --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informations</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="text-muted ps-3">Référence (SKU)</td>
                        <td class="text-end pe-3"><code>{{ $product->sku ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Type</td>
                        <td class="text-end pe-3">{{ $product->type === 'service' ? 'Service' : 'Produit' }}</td>
                    </tr>
                    @if($product->category)
                    <tr>
                        <td class="text-muted ps-3">Catégorie</td>
                        <td class="text-end pe-3">{{ $product->category->name }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted ps-3">Créé le</td>
                        <td class="text-end pe-3">{{ $product->created_at->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-3">Modifié le</td>
                        <td class="text-end pe-3">{{ $product->updated_at->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@if($product->images->count() > 0)
@push('styles')
<style>
.gallery-thumb .gallery-overlay {
    background: rgba(0,0,0,0);
    transition: background .2s ease;
}
.gallery-thumb .gallery-overlay i {
    opacity: 0;
    transform: scale(0.7);
    transition: opacity .2s ease, transform .2s ease;
}
.gallery-thumb:hover .gallery-overlay {
    background: rgba(0,0,0,0.35);
}
.gallery-thumb:hover .gallery-overlay i {
    opacity: 1;
    transform: scale(1);
}
.gallery-thumb:hover img {
    transform: scale(1.06);
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const images = @json($galleryImages);
    let current = 0;
    const modal   = new bootstrap.Modal(document.getElementById('galleryLightbox'));
    const img     = document.getElementById('lightboxImg');
    const caption = document.getElementById('lightboxCaption');
    const prev    = document.getElementById('lightboxPrev');
    const next    = document.getElementById('lightboxNext');

    function show(index) {
        current = (index + images.length) % images.length;
        img.style.opacity = '0';
        setTimeout(() => {
            img.src = images[current].url;
            img.alt = images[current].alt;
            caption.textContent = images[current].alt +
                (images[current].primary ? ' ★ Image principale' : '') +
                '  (' + (current + 1) + ' / ' + images.length + ')';
            img.style.opacity = '1';
        }, 150);
        prev.style.display = images.length > 1 ? '' : 'none';
        next.style.display = images.length > 1 ? '' : 'none';
    }

    document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            show(parseInt(this.dataset.index));
            modal.show();
        });
    });

    prev.addEventListener('click', () => show(current - 1));
    next.addEventListener('click', () => show(current + 1));

    document.getElementById('galleryLightbox').addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft')  show(current - 1);
        if (e.key === 'ArrowRight') show(current + 1);
        if (e.key === 'Escape')     modal.hide();
    });
})();
</script>
@endpush
@endif
