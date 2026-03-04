@extends('layouts.admin')

@section('title', 'Nouveau site')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Sites</a></li>
<li class="breadcrumb-item active">Nouveau site</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-plus me-2"></i>Nouveau site</h1>
        <p class="text-muted mb-0">Créer un nouveau point de vente ou succursale</p>
    </div>
    <a href="{{ route('admin.sites.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>
@endsection

@section('content')
@include('admin.sites.form')
@endsection
