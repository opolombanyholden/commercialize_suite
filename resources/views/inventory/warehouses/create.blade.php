@extends('layouts.admin')
@section('title', 'Nouvel entrepôt')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Stock</a></li>
<li class="breadcrumb-item"><a href="{{ route('inventory.warehouses.index') }}">Entrepôts</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection
@section('page-header')
<h1 class="page-title mb-0">Nouvel entrepôt</h1>
@endsection
@section('content')
@include('inventory.warehouses._form')
@endsection
