@extends('layouts.admin')

@section('title', 'Modifier ' . $delivery->delivery_number)

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('deliveries.index') }}">Bons de livraison</a></li>
<li class="breadcrumb-item"><a href="{{ route('deliveries.show', $delivery) }}">{{ $delivery->delivery_number }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<h1 class="page-title mb-0">Modifier {{ $delivery->delivery_number }}</h1>
@endsection

@section('content')
@php $selectedClient = null; $fromInvoice = null; @endphp
@include('deliveries.form')
@endsection
