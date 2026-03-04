@extends('layouts.admin')

@section('title', 'Modifier ' . $user->name)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-user-edit me-2"></i>Modifier {{ $user->name }}</h1>
        <p class="text-muted mb-0">{{ $user->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-primary">
            <i class="fas fa-eye me-2"></i>Voir le profil
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Retour
        </a>
    </div>
</div>
@endsection

@section('content')
@include('admin.users.form')
@endsection
