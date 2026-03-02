@extends('layouts.admin')

@section('title', 'Catégories de produits')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
    <li class="breadcrumb-item active">Catégories</li>
@endsection

@section('page-header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title">Catégories de produits</h1>
            <p class="text-muted mb-0">Organisez vos produits par catégories</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Nouvelle catégorie
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if($categories->isEmpty())
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune catégorie</h5>
                    <p class="text-muted">Créez votre première catégorie pour organiser vos produits.</p>
                    <a href="{{ route('categories.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Créer une catégorie
                    </a>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-folder me-2 text-primary"></i>Toutes les catégories
                    </h5>
                    <span class="badge bg-secondary">{{ $categories->count() }} catégorie(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Catégorie</th>
                                    <th class="d-none d-md-table-cell">Description</th>
                                    <th class="text-center">Produits</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-center d-none d-md-table-cell">En ligne</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    @include('products.categories._row', ['category' => $category, 'level' => 0])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// Drag & drop reorder (simple version)
document.querySelectorAll('.btn-toggle-status').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        this.closest('form').submit();
    });
});
</script>
@endpush
