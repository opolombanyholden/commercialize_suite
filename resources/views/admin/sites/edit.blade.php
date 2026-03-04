@extends('layouts.admin')

@section('title', 'Modifier le site')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.sites.index') }}">Sites</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.sites.show', $site) }}">{{ $site->name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier le site</h1>
        <p class="text-muted mb-0">{{ $site->name }}{{ $site->code ? ' · ' . $site->code : '' }}</p>
    </div>
    <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Retour
    </a>
</div>
@endsection

@section('content')
@include('admin.sites.form')
@endsection
