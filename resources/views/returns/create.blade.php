@extends('layouts.admin')

@section('title', 'Nouveau retour client')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('returns.index') }}">Retours clients</a></li>
<li class="breadcrumb-item active">Nouveau retour</li>
@endsection

@section('page-header')
<h1 class="page-title mb-0">Nouveau retour client</h1>
@endsection

@section('content')
@include('returns.form')
@endsection
