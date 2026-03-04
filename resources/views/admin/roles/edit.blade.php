@extends('layouts.admin')

@section('title', 'Modifier ' . $role->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Rôles</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier {{ $role->name }}</h1>
        <p class="text-muted mb-0">{{ $role->permissions->count() }} permission(s) assignée(s)</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-primary">
            <i class="fas fa-eye me-2"></i>Voir
        </a>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>
@endsection

@section('content')
<form action="{{ route('admin.roles.update', $role) }}" method="POST">
    @csrf
    @method('PUT')
    @include('admin.roles.form')
</form>
@endsection
