@extends('layouts.admin')
@section('title', 'Modifier l\'entrepôt')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.warehouses.index') }}">Entrepôts</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.warehouses.show', $warehouse) }}">{{ $warehouse->name }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection
@section('page-header')
<h1 class="page-title mb-0">Modifier — {{ $warehouse->name }}</h1>
@endsection
@section('content')
@include('inventory.warehouses._form')
@endsection
