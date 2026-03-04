@extends('layouts.admin')

@section('title', 'Nouveau rôle')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Rôles</a></li>
<li class="breadcrumb-item active">Nouveau rôle</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-plus me-2"></i>Nouveau rôle</h1>
        <p class="text-muted mb-0">Créer un rôle avec des permissions personnalisées</p>
    </div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>
@endsection

@section('content')
@php $rolePermissions = []; @endphp
<form action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    @include('admin.roles.form')
</form>
@endsection
