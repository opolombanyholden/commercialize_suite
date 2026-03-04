@extends('layouts.admin')

@section('title', 'Nouvel utilisateur')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item active">Nouvel utilisateur</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-user-plus me-2"></i>Nouvel utilisateur</h1>
        <p class="text-muted mb-0">Créer un compte utilisateur pour votre entreprise</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>
@endsection

@section('content')
@include('admin.users.form')
@endsection
