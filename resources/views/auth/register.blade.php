@extends('layouts.auth')

@section('title', 'Inscription')
@section('auth-title', 'Créer un compte')
@section('auth-subtitle', 'Essai gratuit de 14 jours, sans engagement')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf
    
    {{-- Name --}}
    <div class="form-floating mb-3">
        <input 
            type="text" 
            class="form-control @error('name') is-invalid @enderror" 
            id="name" 
            name="name" 
            placeholder="Votre nom complet"
            value="{{ old('name') }}"
            required 
            autofocus
        >
        <label for="name"><i class="fas fa-user me-2"></i>Nom complet</label>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
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
        >
        <label for="email"><i class="fas fa-envelope me-2"></i>Adresse email</label>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Company Name --}}
    <div class="form-floating mb-3">
        <input 
            type="text" 
            class="form-control @error('company_name') is-invalid @enderror" 
            id="company_name" 
            name="company_name" 
            placeholder="Nom de votre entreprise"
            value="{{ old('company_name') }}"
            required
        >
        <label for="company_name"><i class="fas fa-building me-2"></i>Nom de l'entreprise</label>
        @error('company_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Phone (optional) --}}
    <div class="form-floating mb-3">
        <input 
            type="tel" 
            class="form-control @error('phone') is-invalid @enderror" 
            id="phone" 
            name="phone" 
            placeholder="+241 XX XX XX XX"
            value="{{ old('phone') }}"
        >
        <label for="phone"><i class="fas fa-phone me-2"></i>Téléphone (optionnel)</label>
        @error('phone')
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
            minlength="8"
        >
        <label for="password"><i class="fas fa-lock me-2"></i>Mot de passe</label>
        <button type="button" class="password-toggle">
            <i class="fas fa-eye"></i>
        </button>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">Minimum 8 caractères</div>
    </div>
    
    {{-- Password Confirmation --}}
    <div class="form-floating mb-3 position-relative">
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
    
    {{-- Terms --}}
    <div class="form-check mb-4">
        <input 
            type="checkbox" 
            class="form-check-input @error('terms') is-invalid @enderror" 
            id="terms" 
            name="terms"
            required
            {{ old('terms') ? 'checked' : '' }}
        >
        <label class="form-check-label" for="terms">
            J'accepte les <a href="#" target="_blank">conditions d'utilisation</a>
            et la <a href="#" target="_blank">politique de confidentialité</a>
        </label>
        @error('terms')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    {{-- Submit Button --}}
    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-rocket me-2"></i>Démarrer mon essai gratuit
        </button>
    </div>
    
    {{-- Trial Info --}}
    <div class="text-center mt-3">
        <small class="text-muted">
            <i class="fas fa-check-circle text-success me-1"></i>14 jours gratuits
            <span class="mx-2">•</span>
            <i class="fas fa-credit-card text-muted me-1"></i>Sans carte bancaire
        </small>
    </div>
</form>
@endsection

@section('footer')
<p class="mb-0">
    Déjà inscrit ? 
    <a href="{{ route('login') }}">Se connecter</a>
</p>
@endsection
