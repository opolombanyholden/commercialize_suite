@extends('layouts.admin')
@section('title', 'Nouvel inventaire')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.sessions.index') }}">Inventaires</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('page-header')
<h1 class="page-title mb-0">Nouvel inventaire</h1>
@endsection

@section('content')
<form action="{{ route('inventory.sessions.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="card-title mb-0"><i class="fas fa-clipboard-list me-2 text-primary"></i>Informations</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Nom de l'inventaire <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name"
                                   value="{{ old('name', 'Inventaire ' . now()->format('d/m/Y')) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror"
                                   id="date" name="date" value="{{ old('date', now()->toDateString()) }}" required>
                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="site_id" class="form-label">Entrepôt</label>
                            <select class="form-select @error('site_id') is-invalid @enderror" id="site_id" name="site_id">
                                <option value="">— Tous les entrepôts —</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ old('site_id') == $wh->id ? 'selected' : '' }}>
                                        {{ $wh->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('site_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Produits pré-chargés</strong><br>
                        <small>Tous les produits avec suivi de stock activé seront ajoutés automatiquement avec leur stock théorique actuel.</small>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-clipboard-check me-2"></i>Démarrer l'inventaire
                    </button>
                    <a href="{{ route('inventory.sessions.index') }}" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
