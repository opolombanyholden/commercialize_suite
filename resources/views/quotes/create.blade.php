@extends('layouts.admin')

@section('title', 'Nouveau devis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Devis</a></li>
<li class="breadcrumb-item active">Nouveau</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Nouveau devis</h1>
        <p class="text-muted mb-0">Créer un nouveau devis client</p>
    </div>
</div>
@endsection

@section('content')
@include('quotes.form')
@endsection
