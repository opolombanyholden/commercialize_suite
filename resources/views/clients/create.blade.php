@extends('layouts.admin')

@section('title', 'Nouveau client')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Nouveau client</h1>
        <p class="text-muted mb-0">Ajouter un nouveau client à votre base</p>
    </div>
</div>
@endsection

@section('content')
@include('clients.form')
@endsection
