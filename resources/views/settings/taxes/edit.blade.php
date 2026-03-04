@extends('layouts.admin')

@section('title', 'Modifier la taxe')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Tableau de bord</a></li>
        <li class="breadcrumb-item"><a href="{{ route('taxes.index') }}">Taxes</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="page-title"><i class="fas fa-edit me-2"></i>Modifier la taxe</h1>
        </div>
        <div class="col-auto">
            <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('taxes.update', $tax) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('settings.taxes._form')
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ route('taxes.index') }}" class="btn btn-outline-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
