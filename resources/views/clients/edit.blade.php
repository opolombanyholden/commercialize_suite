@extends('layouts.admin')

@section('title', 'Modifier le client')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
<li class="breadcrumb-item"><a href="{{ route('clients.show', $client) }}">{{ Str::limit($client->name, 25) }}</a></li>
<li class="breadcrumb-item active">Modifier</li>
@endsection

@section('page-header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="page-title mb-1">Modifier le client</h1>
        <p class="text-muted mb-0">{{ $client->name }}</p>
    </div>
</div>
@endsection

@section('content')
@include('clients.form')
@endsection
