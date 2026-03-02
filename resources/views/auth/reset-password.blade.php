@extends('layouts.auth')

@section('title', 'Réinitialiser le mot de passe')
@section('auth-title', 'Nouveau mot de passe')
@section('auth-subtitle', 'Choisissez un mot de passe sécurisé')

@section('content')
<form method="POST" action="{{ route('password.update') }}">
    @csrf
    
    {{-- Token --}}
    <input type="hidden" name="token" value="{{ $token }}">
    
    {{-- Email --}}
    <div class="form-floating mb-3">
        <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            id="email" 
            name="email" 
            placeholder="nom@exemple.com"
            value="{{ old('email', $email ?? '') }}"
            required 
            autofocus
        >
        <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- New Password --}}
    <div class="form-floating mb-3 position-relative">
        <input 
            type="password" 
            class="form-control @error('password') is-invalid @enderror" 
            id="password" 
            name="password" 
            placeholder="Nouveau mot de passe"
            required
            minlength="8"
        >
        <label for="password"><i class="fas fa-lock me-2"></i>Nouveau mot de passe</label>
        <button type="button" class="password-toggle">
            <i class="fas fa-eye"></i>
        </button>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Minimum 8 caractères</div>
    </div>
    
    {{-- Confirm Password --}}
    <div class="form-floating mb-4 position-relative">
        <input 
            type="password" 
            class="form-control" 
            id="password_confirmation" 
            name="password_confirmation" 
            placeholder="Confirmer le mot de passe"
            required
        >
        <label for="password_confirmation"><i class="fas fa-lock me-2"></i>Confirmer le mot de passe</label>
        <button type="button" class="password-toggle">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    
    {{-- Submit Button --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-key me-2"></i>Réinitialiser le mot de passe
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mb-0">
    <a href="{{ route('login') }}">
        <i class="fas fa-arrow-left me-1"></i>Retour à la connexion
    </a>
</p>
@endsection
