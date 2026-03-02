@extends('layouts.admin')

@section('title', 'Nouvelle facture')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Factures</a></li>
<li class="breadcrumb-item active">Nouvelle</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Nouvelle facture</h1>
        <p class="text-muted mb-0">Créer une nouvelle facture client</p>
    </div>
</div>
@endsection

@section('content')
@include('invoices.form')
@endsection
