@extends('layouts.admin')

@section('title', 'Nouveau produit')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Nouveau produit</h1>
        <p class="text-muted mb-0">Créer un nouveau produit ou service</p>
    </div>
</div>
@endsection

@section('content')
@include('products.form')
@endsection
