@extends('layouts.auth')

@section('title', 'Connexion')
@section('auth-title', 'Bienvenue')
@section('auth-subtitle', 'Connectez-vous à votre compte')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf
    
    {{-- Email --}}
    <div class="form-floating mb-3">
        <input 
            type="email" 
            class="form-control @error('email') is-invalid @enderror" 
            id="email" 
            name="email" 
            placeholder="nom@exemple.com"
            value="{{ old('email') }}"
            required 
            autofocus
        >
        <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Password --}}
    <div class="form-floating mb-3 position-relative">
        <input 
            type="password" 
            class="form-control @error('password') is-invalid @enderror" 
            id="password" 
            name="password" 
            placeholder="Mot de passe"
            required
        >
        <label for="password"><i class="fas fa-lock me-2"></i>Mot de passe</label>
        <button type="button" class="password-toggle">
            <i class="fas fa-eye"></i>
        </button>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Remember Me & Forgot Password --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
            <input 
                type="checkbox" 
                class="form-check-input" 
                id="remember" 
                name="remember"
                {{ old('remember') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="remember">Se souvenir de moi</label>
        </div>
        <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: var(--primary);">
            Mot de passe oublié ?
        </a>
    </div>
    
    {{-- Submit Button --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
        </button>
    </div>
</form>
@endsection

@section('footer')
<p class="mb-0">
    Pas encore de compte ? 
    <a href="{{ route('register') }}">Créer un compte gratuit</a>
</p>
@endsection
