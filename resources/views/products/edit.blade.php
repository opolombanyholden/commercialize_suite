@extends('layouts.admin')

@section('title', 'Modifier le produit')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('products.index') }}">Produits</a></li>
<li class="breadcrumb-item"><a href="{{ route('products.show', $product) }}">{{ Str::limit($product->name, 30) }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Modifier le produit</h1>
        <p class="text-muted mb-0">{{ $product->name }}</p>
    </div>
</div>
@endsection

@section('content')
@include('products.form')
@endsection
