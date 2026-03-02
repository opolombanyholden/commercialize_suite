@extends('layouts.admin')

@section('title', $feature ?? 'Fonctionnalité')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="mb-4">
            <i class="fas fa-rocket fa-4x text-primary opacity-50"></i>
        </div>
        <h2 class="fw-bold mb-2">{{ $feature ?? 'Fonctionnalité' }}</h2>
        <p class="text-muted mb-4">Cette fonctionnalité est en cours de développement et sera disponible prochainement.</p>
        <a href="{{ route('dashboard') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
        </a>
    </div>
</div>
@endsection
