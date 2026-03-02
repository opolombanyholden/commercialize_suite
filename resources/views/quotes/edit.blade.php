@extends('layouts.admin')

@section('title', 'Modifier le devis')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('quotes.index') }}">Devis</a></li>
<li class="breadcrumb-item"><a href="{{ route('quotes.show', $quote) }}">{{ $quote->quote_number }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Modifier le devis</h1>
        <p class="text-muted mb-0">{{ $quote->quote_number }}</p>
    </div>
</div>
@endsection

@section('content')
@include('quotes.form')
@endsection
