@extends('layouts.admin')

@section('title', 'Nouvelle promotion')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Promotions</a></li>
<li class="breadcrumb-item active">Nouvelle promotion</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-plus me-2"></i>Nouvelle promotion</h1>
        <p class="text-muted mb-0">Créer un code de réduction</p>
    </div>
    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<form action="{{ route('promotions.store') }}" method="POST">
    @csrf
    @include('admin.promotions.form')
</form>
@endsection
