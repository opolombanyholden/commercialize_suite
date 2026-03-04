@extends('layouts.admin')

@section('title', 'Modifier — ' . $promotion->code)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('promotions.index') }}">Promotions</a></li>
<li class="breadcrumb-item active">{{ $promotion->code }}</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1"><i class="fas fa-edit me-2"></i>Modifier la promotion</h1>
        <p class="text-muted mb-0">
            <code class="text-primary fw-bold">{{ $promotion->code }}</code>
            — {{ $promotion->uses_count }} utilisation(s)
        </p>
    </div>
    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Retour
    </a>
</div>
@endsection

@section('content')
<form action="{{ route('promotions.update', $promotion) }}" method="POST">
    @csrf @method('PUT')
    @include('admin.promotions.form')
</form>
@endsection
