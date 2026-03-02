@extends('layouts.admin')

@section('title', 'Modifier la facture')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('invoices.index') }}">Factures</a></li>
<li class="breadcrumb-item"><a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Modifier la facture</h1>
        <p class="text-muted mb-0">{{ $invoice->invoice_number }}</p>
    </div>
</div>
@endsection

@section('content')
@include('invoices.form')
@endsection
