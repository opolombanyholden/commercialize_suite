@extends('layouts.admin')

@section('title', 'Nouveau bon de livraison')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Bons de livraison</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('page-header')
<h1 class="page-title mb-0">Nouveau bon de livraison</h1>
@endsection

@section('content')
@include('deliveries.form')
@endsection
